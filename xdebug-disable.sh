#/bin/bash

# Disable the extension
docker exec autoies_nginx sudo rm /etc/php/8.1/mods-available/xdebug.ini

# Restart fpm
docker exec autoies_nginx sudo /etc/init.d/php8.1-fpm restart

echo ""
echo "XDebug disabled !"
echo ""
