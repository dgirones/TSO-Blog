# TSO Theme — Cloud Agent base image
# Provides the stable system toolchain (PHP CLI + extensions + WP-CLI) needed to
# run WordPress with this theme. Repository-specific setup (WordPress core, DB,
# theme symlink, sample content) lives in .cursor/install.sh instead.
FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

# PHP 8.3 CLI + extensions required by WordPress core and the SQLite drop-in,
# plus git/curl/unzip for checkout and setup.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        php-cli \
        php-sqlite3 \
        php-mysql \
        php-curl \
        php-xml \
        php-mbstring \
        php-gd \
        php-zip \
        php-intl \
        ca-certificates \
        curl \
        git \
        unzip \
        less \
    && rm -rf /var/lib/apt/lists/*

# WP-CLI (pinned phar from the official build channel).
RUN curl -fsSL -o /usr/local/bin/wp \
        https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# ubuntu:24.04 ships a default non-root "ubuntu" user (uid 1000) that Cloud
# Agents run as.
USER ubuntu
