FROM php:7.4-fpm-alpine

WORKDIR /app

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY /account-api .

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

RUN composer dump-autoload

RUN chown -R www-data:www-data .

EXPOSE 8080

CMD [ "php", "-S", "0.0.0.0:8080", "-t", "/app/public", "-c", "php.ini" ]