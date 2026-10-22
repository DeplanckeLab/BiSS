# syntax=docker/dockerfile:1

# BiSS (Binding Site Scanner) web server image.
#
# PHP 8.5.12 is the first PHP 8.5 release fixing CVE-2026-109618 and
# CVE-2026-109619 ("php2shell", https://calif.io/php2shell/). Never lower it.
ARG PHP_VERSION=8.5.12
ARG MEME_VERSION=5.5.9
ARG MEME_SHA256=0406fb7b1dc27f6aab3d6d3a29ecdf617bbdd946690cfa97ca2129bc210bfd12

########################################################################
# Stage 1: build FIMO (MEME Suite) from source
########################################################################
FROM debian:trixie-slim AS meme-build
ARG MEME_VERSION
ARG MEME_SHA256

RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		build-essential ca-certificates curl perl python3 \
		zlib1g-dev libxml2-dev libxslt1-dev \
	&& rm -rf /var/lib/apt/lists/*

WORKDIR /usr/src
RUN curl -fsSL -o meme.tar.gz \
		"https://meme-suite.org/meme/meme-software/${MEME_VERSION}/meme-${MEME_VERSION}.tar.gz" \
	&& echo "${MEME_SHA256}  meme.tar.gz" | sha256sum -c - \
	&& tar xzf meme.tar.gz \
	&& cd "meme-${MEME_VERSION}" \
	&& ./configure --prefix=/opt/meme --enable-serial \
	&& make -j"$(nproc)" \
	&& make install

########################################################################
# Stage 2: runtime (Apache + mod_php)
########################################################################
FROM php:${PHP_VERSION}-apache-trixie

# Refuse to build on a PHP vulnerable to CVE-2026-109618 / CVE-2026-109619
RUN php -r 'exit(version_compare(PHP_VERSION, "8.5.12", ">=") ? 0 : 1);' \
	|| { echo "PHP $(php -r 'echo PHP_VERSION;') is vulnerable to php2shell, use >= 8.5.12" >&2; exit 1; }

# Runtime tools called by run_fimo.sh: FIMO's shared libraries and Python 3
RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y --no-install-recommends libxslt1.1 python3-minimal \
	&& rm -rf /var/lib/apt/lists/*

COPY --from=meme-build /opt/meme /opt/meme

# PHP: production defaults + BiSS hardening
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-biss.ini"

# Apache: drop unneeded modules, add BiSS hardening
COPY docker/apache-biss.conf /etc/apache2/conf-available/zz-biss.conf
RUN a2dismod -f autoindex status \
	&& a2enmod headers \
	&& a2disconf serve-cgi-bin \
	&& a2enconf zz-biss

# Application code: owned by root, read-only for the web server
COPY . /var/www/html/
RUN rm -rf /var/www/html/docker \
	&& chmod -R a+rX,go-w /var/www/html

COPY --chmod=0755 docker/entrypoint.sh /usr/local/bin/biss-entrypoint
COPY --chmod=0755 docker/cleanup.sh /usr/local/bin/biss-cleanup

# Jobs and PHP sessions live in a volume, outside the web root
ENV BISS_JOBS_DIR=/data/biss/jobs \
	BISS_SESSIONS_DIR=/data/biss/sessions \
	BISS_RETENTION_DAYS=7
VOLUME /data/biss

HEALTHCHECK --interval=1m --timeout=10s --start-period=20s --retries=3 \
	CMD curl -fsS -o /dev/null http://localhost/submit_page.php || exit 1

ENTRYPOINT ["biss-entrypoint"]
CMD ["apache2-foreground"]
