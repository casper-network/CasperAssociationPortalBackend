FROM composer:2.5.4 AS build-php

WORKDIR /app
COPY . ./

ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/2.0.2/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions \
  && install-php-extensions gd gmp mysqli \
  && composer install --optimize-autoloader --no-interaction --no-progress --no-ansi \
  && composer dump-autoload --optimize

FROM trafex/php-nginx:3.0.0 AS serve

USER root

WORKDIR /app

COPY --chown=nginx config/default.conf /etc/nginx/conf.d/default.conf

RUN apk add --no-cache gcompat=1.1.0-r0 php81-zip php81-mysqli php81-bcmath php81-gmp php81-sqlite3 \
  && rm -rf /var/www/html \
  && rm -rf /var/cache/apk/* \
  && chown -R nginx:nginx /app /var/lib/nginx /run

COPY --chown=nginx --from=build-php /app .

RUN sed -i 's/;extension=zip/extension=zip/' /etc/php81/php.ini \
  && sed -i 's/;extension=bcmath/extension=bcmath/' /etc/php81/php.ini \
  && chmod -R 775 . \
  && chown -R nginx:nginx . \
  && chown -R nginx:nginx /var/log/nginx

USER nobody

# Healthcheck NGINX is running
HEALTHCHECK --interval=5s --timeout=3s --start-period=5s --retries=3 CMD curl --fail http://localhost:8090/ || exit 1

EXPOSE 8090
