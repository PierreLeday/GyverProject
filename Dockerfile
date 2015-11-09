# VERSION:		  1
# DESCRIPTION:	  Create the env for the project
# AUTHOR:		  Gauvin Thibaut  <gauvin.thibaut83@gmail.com>
# COMMENTS:
#	This file describes how to build environment for the Gyver Project.
#	It is a Symfony 2.7.6 Project and we use :
#	PHP (of course), Foundation, Sass, Compass, Nginx & Bower, NodeJs & Ruby
#	Tested on Ubuntu 14.04
#
#
# FIRST USAGE:
#	# Build GyverProject image :
#	docker build -t base_image .
#
#   # Run container :
#   docker run -ti -d -p 999:80 -p 1080:1080 -p 1025:1025 --name gyverproject -v /path/to/your/project:/home/app base_image
#
#   # Connect running container :
#   docker exec -ti <Container ID> bash -l
#
#   # Setup project dependencies :
#   ./entrypoint.sh
#
#
# ON EVERY REBOOT
#   # Starting stopped container :
#   docker start <Container ID>
#
#   # Connect running container :
#   docker exec -ti <Container ID> bash -l
#
#   # Start Process :
#   ./gyver.sh
#


FROM rhyu/ubuntu:latest
ENV TERM linux
MAINTAINER Gauvin Thibaut



# Base
RUN apt-get update && apt-get install -y \
  bash \
  nginx \
  python \
  php5-fpm \
  php5-cli \
  php5-mysql \
  php5-json \
  php5-intl \
  php5-curl \
  dialog \
  net-tools



# Install NodeJs
RUN add-apt-repository ppa:chris-lea/node.js && \
  apt-get update && \
  apt-get install -y nodejs



# Install Bower
RUN npm install -g bower



# Configure Nginx  
ADD app/config/docker/default /etc/nginx/sites-available/default



# Configure PHP
RUN cd /etc/php5/cli && \
  mv php.ini php.ini.backup && \
  ln -s ../fpm/php.ini

ADD app/config/docker/php.ini /etc/php5/fpm/php.ini



# Install Ruby
RUN apt-get -y update
RUN curl -sSL https://rvm.io/mpapis.asc | gpg --import -
RUN curl -L https://get.rvm.io | bash -s stable

RUN /bin/bash -l -c "rvm requirements"
RUN /bin/bash -l -c "rvm install 2.2.1"
RUN /bin/bash -l -c "rvm use 2.2.1"



# Install Composer
RUN curl -sS https://getcomposer.org/installer | php
RUN chmod +x composer.phar
RUN mv composer.phar /usr/local/bin/composer



ADD app/config/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ADD app/config/docker/gyver.sh /gyver.sh
RUN chmod +x /gyver.sh



EXPOSE 999
EXPOSE 1080
EXPOSE 1025

VOLUME ["/home/app"]

#   docker run -ti -d --name gyver_mysql -e MYSQL_ROOT_PASSWORD=mypassword mysql:latest
#   docker run -ti -d -p 999:80 -p 1080:1080 -p 1025:1025 -p 3307:3306 --link gyver_mysql:mysql --name gyverproject -v /home/app/php/GyverProject:/home/app app