#base img
FROM php:8.2-apache
RUN a2enmod rewrite
#work dir
WORKDIR /var/www/html/smart_greenhouse_back_end
#copy files
COPY . .
# install required PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    supervisor\
    git \
    && docker-php-ext-install sockets \
    && apt-get clean && rm -rf /var/lib/apt/lists/*
#laravel supervisor conf
COPY ./supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#start supervisord

#set permissions
RUN chown -R www-data:www-data /var/www/html/smart_greenhouse_back_end/storage /var/www/html/smart_greenhouse_back_end/bootstrap/cache
#copy virtual host file
COPY ./apache-laravel.conf /etc/apache2/sites-available/000-default.conf
#start apache server
# CMD ["apache2-foreground"]
#install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"
RUN composer install --no-interaction --prefer-dist --optimize-autoloader
# RUN composer update
#export ports
EXPOSE 8000
# start apache
CMD ["apache2-foreground"]




