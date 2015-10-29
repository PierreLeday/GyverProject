# VERSION:		  0.1
# DESCRIPTION:	  Create the env for the project
# AUTHOR:		  Gauvin Thibaut  <gauvin.thibaut83@gmail.com>
# COMMENTS:
#	This file describes how to build environment for the Gyver Project.
#	It is a Symfony 2.7.6 Project and we use :
#	PHP (of course), Foundation, Sass, Compass, Nginx & Bower
#	Tested on Ubuntu 14.04
#
# USAGE:
#	# Build GyverProject image :
#	docker build -t GyverProject .
#
#   # Run container in daemon mode & Open port 999 & Mount shared directory :
#   docker run -ti -d -p 999:80 --name GyverProject -v /path/to/code/local:/home/app GyverProject
#
#   # Connect to running container :
#   docker exec -ti <hashContainerID> bash -l
#

FROM ubuntu:14.04
MAINTAINER Gauvin Thibaut



# Base
RUN apt-get update && apt-get install -y \
  git \
  curl \
  multitail \
  htop \
  nano \
  nginx \
  python-software-properties \
  software-properties-common \
  python \
  php5-fpm \
  php5-cli \
  php5-mysql \
  php5-json \
  php5-intl \
  php5-curl \
  build-essential



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
RUN chmod +x composer.phar \
RUN mv composer.phar /usr/local/bin/composer




# Install Dep & compile asset:
# RUN gem install bundler
# RUN cd /home/app && \
#   bundle install && \
#   bower install && \
#   compass compile



ADD app/config/docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 999
VOLUME ["/home/app"]
WORKDIR /home/app



# ENTRYPOINT ["/entrypoint.sh"]
