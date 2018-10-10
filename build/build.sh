#!/usr/bin/env sh

composer install \
        --no-dev \
        --prefer-dist \
        --no-suggest \
        --optimize-autoloader \
        --classmap-authoritative \
        --no-interaction \
    || { echo "ERROR: failed to install dependencies" >&2; exit 1; }
box build
sha1sum dobr.phar > dobr.phar.version
