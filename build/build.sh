#!/usr/bin/env sh

composer install --no-dev --no-interaction \
    || { echo "ERROR: failed to install dependencies" >&2; exit 1; }
box compile
sha1sum dobr.phar >> dobr.version
