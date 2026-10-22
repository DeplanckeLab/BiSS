#! /bin/bash
# Deletes BiSS user data (one folder per session) that has not been touched
# for more than BISS_RETENTION_DAYS days, plus stale PHP session files.
# Usage: biss-cleanup          # run once
#        biss-cleanup --loop   # run every BISS_CLEANUP_INTERVAL seconds

JOBS_DIR="${BISS_JOBS_DIR:-/data/biss/jobs}"
SESSIONS_DIR="${BISS_SESSIONS_DIR:-/data/biss/sessions}"
RETENTION_DAYS="${BISS_RETENTION_DAYS:-7}"
INTERVAL="${BISS_CLEANUP_INTERVAL:-3600}"

clean()
{
	if [ -d "$JOBS_DIR" ]
	then
		for dir in "$JOBS_DIR"/*/
		do
			[ -d "$dir" ] || continue
			# Keep the folder if anything inside is recent enough
			if [ -z "$(find "$dir" -mtime "-$RETENTION_DAYS" -print -quit)" ]
			then
				echo "$(date -Is) Deleting expired user data ${dir%/}"
				rm -rf -- "$dir"
			fi
		done
	fi
	if [ -d "$SESSIONS_DIR" ]
	then
		find "$SESSIONS_DIR" -maxdepth 1 -type f -name 'sess_*' -mtime "+$RETENTION_DAYS" -delete
	fi
}

if [ "$1" = "--loop" ]
then
	echo "$(date -Is) Cleanup every ${INTERVAL}s, keeping data for ${RETENTION_DAYS} days"
	while true
	do
		clean
		sleep "$INTERVAL"
	done
else
	clean
fi
