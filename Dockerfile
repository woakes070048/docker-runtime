FROM ubuntu:20.04
LABEL maintainer "Sitepilot <support@sitepilot.io>"

# ----- Environment ----- #
ENV PHP_VERSION=7.4
ENV PATH="/opt/sitepilot/bin:${PATH}"

ENV APP_PATH=/opt/sitepilot/app
ENV APP_PATH_PUBLIC=/opt/sitepilot/app/public
ENV APP_PATH_DEPLOY=/opt/sitepilot/app/deploy
ENV APP_PATH_SSH=/opt/sitepilot/app/.ssh
ENV COMPOSER_HOME=/opt/sitepilot/app/.composer

# ----- Build Files ----- #

COPY build /

# ----- Common ----- #

RUN install-packages sudo software-properties-common supervisor curl wget gpg-agent unzip mysql-client git ssh msmtp nano openssh-server zsh

# ----- OpenResty ----- #

RUN wget -qO - https://openresty.org/package/pubkey.gpg | apt-key add - \
    && add-apt-repository -y "deb http://openresty.org/package/ubuntu $(lsb_release -sc) main" \
    && install-packages openresty \
    && openresty -v

# ----- PHP ----- #

RUN add-apt-repository ppa:ondrej/php -y \
    && install-packages php${PHP_VERSION}-fpm php${PHP_VERSION}-common php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-xml php${PHP_VERSION}-xmlrpc php${PHP_VERSION}-curl php${PHP_VERSION}-gd \
    php${PHP_VERSION}-imagick php${PHP_VERSION}-cli php${PHP_VERSION}-dev php${PHP_VERSION}-imap \
    php${PHP_VERSION}-mbstring php${PHP_VERSION}-opcache php${PHP_VERSION}-redis \
    php${PHP_VERSION}-soap php${PHP_VERSION}-zip \
    && mkdir -p /run/php \
    && php-fpm${PHP_VERSION} -v

# ----- Composer ----- #

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --version=1.10.16 \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version

# ----- WPCLI ----- #

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x wp-cli.phar \
    && mv wp-cli.phar /usr/local/bin/wp \
    && wp --allow-root --version

# ----- Runtime ----- #

RUN wget https://github.com/sitepilot/runtime/releases/latest/download/runtime -O /opt/sitepilot/bin/runtime \
    && chmod +x /opt/sitepilot/bin/runtime \
    && runtime --version

# ----- Webhook ----- #

RUN install-packages webhook

# ----- NodeJS ----- #

RUN curl -sL https://deb.nodesource.com/setup_12.x | sudo bash - \
    && install-packages nodejs \
    && npm -v \
    && node -v

# ------ User ----- #

RUN echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers \
    && usermod -d /opt/sitepilot/app www-data \
    && chsh -s /bin/zsh www-data \
    && git clone https://github.com/ohmyzsh/ohmyzsh.git /opt/sitepilot/ohmyzsh \
    && chmod +x /opt/sitepilot/ohmyzsh/oh-my-zsh.sh

# ----- Files ----- #

COPY filesystem /

RUN mkdir /var/www \
    && mkdir -p /opt/sitepilot/etc \
    && mkdir -p /var/lib/nginx/logs \
    && chown -R www-data:www-data /run \
    && chown -R www-data:www-data /opt/sitepilot \
    && chown -R www-data:www-data /var/lib/nginx \
    && chown -R www-data:www-data /var/www \
    && rm -f /etc/update-motd.d/*

RUN ln -sf /opt/sitepilot/etc/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/zz-01-custom.ini

# ----- Config ----- #

EXPOSE 8080

USER 33:33

WORKDIR /opt/sitepilot/app

ENTRYPOINT ["/opt/sitepilot/bin/entrypoint"]

CMD ["supervisord", "-c", "/opt/sitepilot/etc/supervisor.conf"]
