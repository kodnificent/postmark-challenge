FROM phpswoole/swoole:php8.3-alpine

ENV SUPERVISOR_OCTANE_COMMAND="php /var/www/artisan octane:start --server=swoole --host=0.0.0.0 --port=8000"

RUN apk add --no-cache \
    nginx \
    curl \
    nodejs \
    npm \
    supervisor \
    ca-certificates \
    wget \
    ffmpeg \
    poppler-utils

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql exif pcntl sockets gd intl redis zip imagick/imagick@master

RUN if [[ $(uname -m) == "aarch64" ]] ; \
    then \
    # aarch64
    wget https://raw.githubusercontent.com/squishyu/alpine-pkg-glibc-aarch64-bin/master/glibc-2.26-r1.apk ; \
    apk add --no-cache --allow-untrusted --force-overwrite glibc-2.26-r1.apk ; \
    rm glibc-2.26-r1.apk ; \
    else \
    # x86_64
    wget https://github.com/sgerrand/alpine-pkg-glibc/releases/download/2.28-r0/glibc-2.28-r0.apk ; \
    wget -q -O /etc/apk/keys/sgerrand.rsa.pub https://alpine-pkgs.sgerrand.com/sgerrand.rsa.pub ; \
    apk add --no-cache --force-overwrite glibc-2.28-r0.apk ; \
    rm glibc-2.28-r0.apk ; \
    fi

COPY composer.json composer.lock /var/www/
COPY --from=composer:2.3 /usr/bin/composer /usr/bin/composer

COPY package.json bun.lock /var/www/
RUN npm install -g bun

COPY .docker .docker
RUN if [ -f .docker/app/site.conf ]; then cp .docker/app/site.conf /etc/nginx/http.d/default.conf; fi
RUN if [ -f .docker/app/common.conf ]; then cp .docker/app/site.conf /etc/nginx/common.conf; fi
COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/php/php.ini $PHP_INI_DIR/php.ini
COPY .docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN addgroup -S -g 1000 www && adduser --shell /bin/sh -S -G www -u 1000 www

RUN mkdir -p /var/lib/nginx/tmp/ \
    && mkdir -p /var/log/nginx \
    && touch /var/run/nginx.pid \
    && touch /var/run/supervisord.pid \
    && chown -R www:www /var/lib/nginx \
    && chown -R www:www /var/log/nginx \
    && chown -R www:www /var/run/supervisord.pid \
    && chown -R www:www /var/run/nginx.pid \
    && chown -R www:www /var/run/nginx

COPY . /var/www

RUN chown -R www:www /var/www \
    && chmod -R 755 /var/www

# generate self-signed SSL certs for local dev
RUN mkdir -p /etc/letsencrypt/live/domain.com && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/letsencrypt/live/domain.com/privkey.pem \
    -out /etc/letsencrypt/live/domain.com/fullchain.pem \
    -subj "/C=US/ST=Denial/L=Springfield/O=Dis/CN=localhost" && \
    chmod 644 /etc/letsencrypt/live/domain.com/*.pem

USER www
WORKDIR /var/www

RUN composer install --optimize-autoloader --no-dev --ansi --no-scripts

RUN bun install && \
    bun run build

EXPOSE 80 443 5173 9003
STOPSIGNAL SIGQUIT
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
