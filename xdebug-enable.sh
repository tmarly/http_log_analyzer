#/bin/bash

#Mac
if [ "$(uname)" == "Darwin" ]; then
  #Create an alias on mac
  sudo ifconfig lo0 alias 10.254.254.254
  HOST_IP=10.254.254.254
#Linux
else
  HOST_IP=$(docker inspect -f '{{range .NetworkSettings.Networks}}{{.Gateway}}{{end}}' autoies_nginx)
fi



# Enable the extension
docker exec autoies_nginx sudo sed -i "s/%HOST_IP%/$HOST_IP/g" /etc/php/8.1/mods-available/xdebug.ini.dist
docker exec autoies_nginx sudo cp -f /etc/php/8.1/mods-available/xdebug.ini.dist /etc/php/8.1/mods-available/xdebug.ini

# Restart fpm
docker exec autoies_nginx sudo /etc/init.d/php8.1-fpm restart

echo ""
echo "XDebug enabled !"
echo ""
echo "To use it with PHPStorm, follow these instructions:"
echo "https://git.clever-age.net/autoies/tools/blob/master/docker/scripts/xdebug/README.md"
echo ""
