FROM oneguard/php:7.2-cli

RUN apk add --no-cache git \
    && echo 'phar.readonly = 0' > /usr/local/etc/php/conf.d/allow-write-phar.ini \
    && curl -LSs https://box-project.github.io/box2/installer.php | php \
    && mv box.phar /usr/local/bin/box \
    && box --version
