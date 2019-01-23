FROM php:7.1.26-apache

EXPOSE 80

RUN apt-get update
RUN apt-get install -y autoconf g++ make openssl libssl-dev libcurl4-openssl-dev
RUN apt-get install -y libcurl4-openssl-dev pkg-config
RUN apt-get install -y libsasl2-dev

COPY ./config/000-default.conf /etc/apache2/sites-enabled/

EXPOSE 80

RUN apt-get update
RUN apt-get install -y autoconf g++ make openssl libssl-dev libcurl4-openssl-dev
RUN apt-get install -y libcurl4-openssl-dev pkg-config
RUN apt-get install -y libsasl2-dev

# Copying over a better VirtualHost setup.
COPY ./config/000-default.conf /etc/apache2/sites-enabled/

# Create the logs dir for our apache2
RUN mkdir -p /var/www/html/logs

# Enable mod_rewrite
RUN a2enmod rewrite

# Enabling opcache
COPY ./config/php-ext-opcache.ini /usr/local/etc/php/conf.d/

# Adding general PHP config options
COPY ./config/php-00-general.ini /user/local/etc/php/conf.d/

# Installing composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN chmod a+x composer.phar
RUN mv composer.phar /usr/bin/composer

ADD . /tmp




