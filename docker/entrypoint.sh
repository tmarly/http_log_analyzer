#!/bin/bash

# First: run nginx so the container is usable, even if this 
# script is not terminated.
#
# Note:
# We do not run php-fpm in foreground (php-fpm8.1 -F)
# because sometimes it's more convenient to restart fpm without killing the container
# cf. scripts/xdebug-enable.sh for example.
# Same for nginx, sometimes usefull to restart the service without killing the container.
sudo /etc/init.d/nginx start
sudo /etc/init.d/php8.1-fpm start

tail -f /dev/null
