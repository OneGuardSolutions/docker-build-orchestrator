FROM oneguard/php:7.2-cli

RUN apk add --no-cache git gnupg \
    && wget https://phar.io/releases/phive.phar \
    && wget https://phar.io/releases/phive.phar.asc \
    && gpg --keyserver hkps.pool.sks-keyservers.net --recv-keys 0x9B2D5D79 \
    && gpg --verify phive.phar.asc phive.phar \
    && rm phive.phar.asc \
    && chmod +x phive.phar \
    && mv phive.phar /usr/local/bin/phive \
    && phive install humbug/box --force-accept-unsigned \
    && ln -s /tools/box /usr/local/bin/box \
    && box --version
