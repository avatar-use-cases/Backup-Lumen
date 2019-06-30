Steps taken to setup Lumen on Ubuntu. (Similar for Amazon EC2 with yum)</br>
</br>
apt update</br>
apt upgrade</br>
</br>
RUN: --------------------> add-apt-repository ppa:ondrej/php</br>
RUN: --------------------> apt update</br>
RUN: --------------------> apt install apache2 libapache2-mod-php7.2</br>
RUN: --------------------> apt install php7.2</br>
RUN: --------------------> apt install php7.2-xml</br>
RUN: --------------------> apt install php7.2-gd</br>
RUN: --------------------> apt install php7.2-opcache</br>
RUN: --------------------> apt install php7.2-mbstring</br>
</br>
MY_DOMAIN="test-cco-domain"</br>
only use sudo where applicable</br>
RUN: ------------> cd /tmp</br>
RUN: ------------> curl -sS https://getcomposer.org/installer | php</br>
RUN: ------------> sudo mv composer.phar /usr/local/bin/composer</br>
RUN: ------------> cd /var/www/html</br>
RUN: ------------> sudo composer create-project laravel/laravel $MY_DOMAIN --prefer-dist</br>
RUN: ------------> sudo chgrp -R www-data /var/www/html/$MY_DOMAIN</br>
RUN: ------------> sudo chmod -R 775 /var/www/html/$MY_DOMAIN/storage</br>
RUN</br>
RUN: ------------> cd /var/www/html/$MY_DOMAIN</br>
RUN: ------------> sudo composer update --no-dev</br>
RUN: ------------> sudo php artisan key:generate</br>
</br>
For more information and guides see: https://laravel.com/docs/5.8/installation</br>
</br>
