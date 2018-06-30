#######################################################################
#                Stalker Laravel App - Dockerfile v0.9                #
#######################################################################

#------------- Setup Environment -------------------------------------------------------------

# Pull base image
FROM ubuntu:16.04

# Install common tools 
RUN apt-get update
RUN apt-get install -y wget curl nano htop git unzip bzip2 software-properties-common locales

# Set evn var to enable xterm terminal
ENV TERM=xterm

# Create and set working directory
WORKDIR /var/www/stalker

#------------- Application Specific Stuff ----------------------------------------------------

# Install PHP
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php
RUN apt update
RUN apt-get install -y \
    php7.2-fpm \ 
    php7.2-common \ 
    php7.2-curl \ 
    php7.2-mysql \ 
    php7.2-mbstring \ 
    php7.2-json \
    php7.2-xml \
    php7.2-zip

#------------- FPM & Nginx configuration ----------------------------------------------------

# Config fpm to use TCP instead of unix socket
ADD resources/www.conf /etc/php/7.2/fpm/pool.d/www.conf

# Install Nginx
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys ABF5BD827BD9BF62
RUN apt-key adv --keyserver keyserver.ubuntu.com --recv-keys 4F4EA0AAE5267A6C
RUN echo "deb http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list
RUN echo "deb-src http://nginx.org/packages/ubuntu/ trusty nginx" >> /etc/apt/sources.list
RUN apt-get update

RUN apt-get install -y nginx

ADD resources/default /etc/nginx/sites-enabled/
ADD resources/nginx.conf /etc/nginx/

#------------- Composer & laravel configuration ----------------------------------------------------

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#------------- Supervisor Process Manager ----------------------------------------------------

# Install supervisor
RUN apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor
ADD resources/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Add entrypoint script make script executable
ADD resources/entrypoint.sh /tmp/resources/entrypoint.sh
RUN chmod +x /tmp/resources/entrypoint.sh

# Create entrypoint logging directory
RUN mkdir -p /var/log/stalker

#------------- Container Config ---------------------------------------------------------------

# Expose port 80
EXPOSE 80

# Set supervisor to manage container processes
ENTRYPOINT ["/usr/bin/supervisord"]
