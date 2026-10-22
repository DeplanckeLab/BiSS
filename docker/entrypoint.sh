#!/bin/sh
# Prepares the data volume, then runs the given command (Apache by default).
set -eu

RETENTION_DAYS="${BISS_RETENTION_DAYS:-7}"
case "$RETENTION_DAYS" in
	''|*[!0-9]*) echo "Error: BISS_RETENTION_DAYS must be a whole number of days" >&2; exit 1 ;;
esac

if [ "$(id -u)" = 0 ]
then
	mkdir -p "$BISS_JOBS_DIR" "$BISS_SESSIONS_DIR"
	chown www-data:www-data "$BISS_JOBS_DIR" "$BISS_SESSIONS_DIR"
	chmod 0770 "$BISS_JOBS_DIR" "$BISS_SESSIONS_DIR"

	# Sessions (and thus access to one's jobs) last as long as the jobs do
	echo "session.gc_maxlifetime = $((RETENTION_DAYS * 86400 + 86400))" \
		> "$PHP_INI_DIR/conf.d/zz-biss-runtime.ini"
fi

exec "$@"
