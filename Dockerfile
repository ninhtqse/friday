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

#ZIP
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions zip
#RUN apt-get -y install gcc make autoconf libc-dev pkg-config libzip-dev
# COPY php.fpm.ini /etc/php7/fpm/php.ini
# COPY php.cli.ini /etc/php7/cli/php.ini

#class image laravel
RUN apt-get install -y libpng-dev
RUN docker-php-ext-install gd


#xdebug
# RUN yes | pecl install xdebug \
#     && docker-php-ext-enable xdebug
#     && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
#     && echo "xdebug.client_host = host.docker.internal" >>
# /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host = host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#cho watermark bị lỗi không đọc được file jpg
# command check : php -r 'print_r(gd_info());'
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN chown www-data:www-data /var
RUN usermod -u 1000 www-data

USER www-data

CMD ["php-fpm"]


