FROM php:7-fpm

### PROD tools ###

##### Composer and tools >>> ########################
RUN apt-get update -q -y \
 && apt-get install -q -y --no-install-recommends \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libbz2-dev \
    libicu-dev \
    zlib1g-dev \
    libzip-dev \
    openssl \
    g++ \
    curl \
    git \
    unzip \
    gettext \
    acl \
    locales \
    locales-all

ENV LANG en_US.UTF-8
ENV LC_ALL en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
RUN echo en_US UTF-8 >> /etc/locale.gen && locale-gen en_US.UTF-8 && DEBIAN_FRONTEND=noninteractive dpkg-reconfigure locales

RUN \
# PHP Extensions pour composer
  docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
  && docker-php-ext-configure intl \
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip gd mbstring iconv pdo pdo_mysql opcache intl calendar \
  && docker-php-ext-enable opcache

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install cron
RUN touch /var/log/cron.log \
    && apt-get install -q -y cron \
    && chmod -R 655 /etc/cron.d && chown -R root:root /etc/cron.d

### DEV tools ###

# Add additional packages for dev use
RUN apt-get install -q -y --no-install-recommends \
    vim \
    wget \
    tree \
    procps \
    gdb-minimal \
    iputils-ping \
    telnet

# Install xdebug for coverage purpose
#RUN pecl install https://xdebug.org/files/xdebug-2.6.1.tgz \
#  && docker-php-ext-enable xdebug

### END ###

# Clean image
RUN \
# Delete source & builds deps so it does not hang around in layers taking up space
  rm -Rf "$(pecl config-get temp_dir)/*" \
  && docker-php-source delete \
  && apt-get purge -y --auto-remove \
# Clean downloaded packages
  && rm -rf /var/lib/apt/lists/*

# Needed for docker-machine
RUN usermod -u 1000 www-data

WORKDIR /var/www

CMD ["php-fpm"]

EXPOSE 9000
