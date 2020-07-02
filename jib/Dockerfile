FROM php:7.3-fpm-alpine

RUN apk --no-cache add \
    nginx supervisor curl postgresql-dev && \
    docker-php-ext-install pgsql pdo_pgsql && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    rm /etc/nginx/conf.d/default.conf && \
    mkdir /app

RUN curl -L -o /tmp/redis.tar.gz https://github.com/phpredis/phpredis/archive/5.3.0.tar.gz \
    && tar -xf /tmp/redis.tar.gz --directory /tmp \
    && rm -r /tmp/redis.tar.gz \
    && mkdir -p /usr/src/php/ext/ && mv /tmp/phpredis-5.3.0 /usr/src/php/ext/redis \
    && docker-php-ext-install redis

COPY .docker/nginx.conf /etc/nginx/nginx.conf
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /app

COPY . /app/

RUN cp .env.example .env && \
    composer --optimize-autoloader install && \
    touch /app/storage/logs/laravel.log && \
    chown -R www-data:www-data /app && \
    chmod -R 755 /app/storage

CMD sleep 10 && php /app/artisan migrate --seed --force && \
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
