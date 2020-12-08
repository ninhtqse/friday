FROM php:7.4-fpm

LABEL maintainer="Ninhtq <ninhtqse@gmail.com>"

# Install VIM and GIT
RUN apt-get update && apt-get install -y \
    git \
    vim \
	unzip

# Install PHP libraries
RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libicu-dev \
		libpng-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 www-data

USER www-data

CMD ["php-fpm"]
