FROM php:7.4-fpm

WORKDIR /var/www

LABEL maintainer="Ninhtq <ninhtqse@gmail.com>"

# Install VIM and GIT
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    cron 

# Install PHP libraries
RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libicu-dev \
		libpng-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql

#class image laravel
RUN sudo apt-get install -y libpng-dev
RUN sudo docker-php-ext-install gd


RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini


# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown www-data:www-data /var
RUN usermod -u 1000 www-data

USER www-data

CMD ["php-fpm"]


