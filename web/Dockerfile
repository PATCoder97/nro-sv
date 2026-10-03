FROM php:8.2-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite headers expires

COPY . /var/www/html
COPY docker/php.ini /usr/local/etc/php/conf.d/teamobi.ini
COPY docker/entrypoint.sh /usr/local/bin/teamobi-web-entrypoint

RUN chmod +x /usr/local/bin/teamobi-web-entrypoint \
    && mkdir -p /var/www/html/logs /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/logs /var/www/html/uploads /var/www/html/images

EXPOSE 80

ENTRYPOINT ["teamobi-web-entrypoint"]
CMD ["apache2-foreground"]
