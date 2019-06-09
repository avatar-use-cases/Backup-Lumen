Steps taken to setup Lumen on Ubuntu. (Similar for Amazon EC2 with yum)

apt update
apt upgrade

RUN: --------------------> add-apt-repository ppa:ondrej/php
RUN: --------------------> apt update
RUN: --------------------> apt install apache2 libapache2-mod-php7.2
RUN: --------------------> apt install php7.2
RUN: --------------------> apt install php7.2-xml
RUN: --------------------> apt install php7.2-gd
RUN: --------------------> apt install php7.2-opcache
RUN: --------------------> apt install php7.2-mbstring

MY_DOMAIN="test-cco-domain"
only use sudo where applicable
RUN: ------------> cd /tmp
RUN: ------------> curl -sS https://getcomposer.org/installer | php
RUN: ------------> sudo mv composer.phar /usr/local/bin/composer
RUN: ------------> cd /var/www/html
RUN: ------------> sudo composer create-project laravel/laravel $MY_DOMAIN --prefer-dist
RUN: ------------> sudo chgrp -R www-data /var/www/html/$MY_DOMAIN
RUN: ------------> sudo chmod -R 775 /var/www/html/$MY_DOMAIN/storage
RUN
RUN: ------------> cd /var/www/html/$MY_DOMAIN
RUN: ------------> sudo composer update --no-dev
RUN: ------------> sudo php artisan key:generate

For more information and guides see: https://laravel.com/docs/5.8/installation
