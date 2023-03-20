FROM ubuntu:20.04

# Timezone, we need to set it now otherwise will be interactively asked later
ENV TZ=Europe/Paris
RUN \
  ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
  && apt-get update \
  && DEBIAN_FRONTEND=noninteractive apt-get install --no-install-recommends -y tzdata

# Add a "docker" user
ARG UID
ARG GID
RUN \
  groupadd docker --non-unique -g ${GID} \
  && useradd docker -u ${UID} -g ${GID} --shell /bin/bash --create-home \
  && usermod --append --groups sudo docker \
  && echo 'ALL ALL = (ALL) NOPASSWD: ALL' >> /etc/sudoers \
  && echo 'docker:secret' | chpasswd


# =============================================================================
# Usefull basic tools
# =============================================================================

# without that, dpkg might ask questions about changed config file (ex: package 'sudo')
RUN echo 'Dpkg::Options { "--force-confdef"; "--force-confold"; }' > /etc/apt/apt.conf.d/local

RUN \
  apt-get update && \
  apt-get install --no-install-recommends -y \
  apt-utils \
  aptitude \
  software-properties-common \
  apt-transport-https \
  ca-certificates \
  gnupg \
  sudo \
  curl \
  wget \
  unzip \
  vim \
  aptitude \
  make \
  procps \
  libpng-dev \
  telnet \
  mysql-client \
  git \
  patch \
  bash-completion

# bash-completion: for symfony command


# =============================================================================
# Install Nginx + PHP
# =============================================================================


RUN \
  sudo add-apt-repository -y ppa:ondrej/php && \
  apt-get update && \
  apt-get install --no-install-recommends -y \
  nginx \
  php8.1-fpm \
  php8.1-mysql \
  php8.1-gd \
  php8.1-curl \
  php8.1-http \
  php8.1-raphf \
  php8.1-bcmath \
  php8.1-cli \
  php8.1-intl \
  php8.1-common \
  php8.1-xml \
  php8.1-mbstring \
  php8.1-zip \
  php8.1-apcu \
  php8.1-xdebug \
  php8.1-imagick \
  php8.1-redis

# Conf PHP-FPM (inspired from the official PHP FPM image)
RUN \
   mkdir -p /var/run/php && \
   chown -R docker:docker /var/run/php && \
   sed -i "s/^user =.*/user = docker/" /etc/php/8.1/fpm/pool.d/www.conf && \
   sed -i "s/^group =.*/group = docker/" /etc/php/8.1/fpm/pool.d/www.conf

# Conf PHP
ADD php/php-cli.ini /etc/php/8.1/cli/conf.d/
ADD php/php-fpm.ini /etc/php/8.1/fpm/conf.d/

# HTTPS / SSL
RUN \
  sudo apt-get update && \
  sudo apt-get install ssl-cert && \
  sudo openssl dhparam -out /etc/ssl/private/dhparam.pem 2048

# Conf Nginx
ADD nginx/sites-available/* /etc/nginx/sites-available/
#ADD nginx/conf.d/*.conf /etc/nginx/conf.d/
ADD nginx/snippets/*.conf /etc/nginx/snippets/
RUN ln -s /etc/nginx/sites-available/*.conf /etc/nginx/sites-enabled/ \
  && rm /etc/nginx/sites-enabled/default 

# Xdebug - by default, disabled
ENV PHP_IDE_CONFIG="serverName=apachehisto"
RUN rm /etc/php/8.1/mods-available/xdebug.ini
ADD php/xdebug.ini.dist /etc/php/8.1/mods-available/


# =============================================================================
# GO !
# =============================================================================

WORKDIR /var/www

USER docker

ADD entrypoint.sh /home/docker/

ENTRYPOINT /home/docker/entrypoint.sh

EXPOSE 80