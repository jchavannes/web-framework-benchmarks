FROM phusion/baseimage

MAINTAINER Jason Chavannes <jason.chavannes@gmail.com>

ENV version 1.0

RUN apt-get update
RUN apt-get -y upgrade
RUN apt-get -y install nodejs npm

CMD nodejs /var/www/node.js
