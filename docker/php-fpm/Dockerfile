FROM php:8.0-fpm-alpine

RUN apk --update --no-cache add git libxml2-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev shadow &&\
docker-php-ext-install pdo_mysql &&\
docker-php-ext-install soap &&\
docker-php-ext-install zip &&\
docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg &&\
docker-php-ext-install gd &&\
docker-php-ext-install fileinfo;

RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug;

RUN echo 'php_admin_value[max_execution_time] = 0' >> /usr/local/etc/php-fpm.d/www.conf &&\
echo 'php_admin_value[post_max_size] = 5G' >> /usr/local/etc/php-fpm.d/www.conf &&\
echo 'php_admin_value[upload_max_filesize] = 5G' >> /usr/local/etc/php-fpm.d/www.conf &&\
echo 'php_admin_value[memory_limit] = 10G' >> /usr/local/etc/php-fpm.d/www.conf &&\
sed -i 's/.*pm.start_servers = 2.*/pm.start_servers = 6/' /usr/local/etc/php-fpm.d/www.conf &&\ 
sed -i 's/.*pm.max_children = 5.*/pm.max_children = 20/' /usr/local/etc/php-fpm.d/www.conf &&\
sed -i 's/.*pm.max_spare_servers = 3.*/pm.max_spare_servers = 7/' /usr/local/etc/php-fpm.d/www.conf;

RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ --allow-untrusted gnu-libiconv &&\
usermod -u 110 www-data && groupmod -g 110 www-data &&\
rm -rf /var/cache/apk/*;mkdir -p /var/www/public/upload ;chown -R www-data:www-data  /var/www/public/upload;



ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

CMD  composer install ;sleep 15 &&php bin/console doctrine:migrations:migrate -n; php bin/console cache:clear; rm -rf /var/www/var/cache/*; chmod -R 777 /var/www/var/;touch  /usr/local/etc/php/xdebug.log ;chmod 777 /usr/local/etc/php/xdebug.log; php-fpm ;


EXPOSE 9000
