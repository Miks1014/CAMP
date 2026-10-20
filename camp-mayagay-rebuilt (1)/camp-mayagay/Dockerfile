FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
 && a2enmod headers

COPY docker/php.ini /usr/local/etc/php/conf.d/camp-mayagay.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html
COPY assets ./assets
COPY php ./php
COPY PHPMailer ./PHPMailer
COPY *.html ./

RUN chmod +x /usr/local/bin/entrypoint.sh \
 && mkdir -p assets/uploads/avatars assets/uploads/valid_ids assets/uploads/accommodations \
 && chown -R www-data:www-data assets/uploads

EXPOSE 80
ENTRYPOINT ["entrypoint.sh"]
