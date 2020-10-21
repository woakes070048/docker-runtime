FROM ubuntu:20.04
LABEL maintainer "Sitepilot <support@sitepilot.io>"

# ----- Environment ----- #

ENV PORT=8080
ENV PHP_VERSION=7.4
ENV DOCKERIZE_VERSION=2.1.0
ENV PATH="/opt/sitepilot/bin:${PATH}"

ENV APP_ROOT=/opt/sitepilot/app
ENV DOCUMENT_ROOT=/opt/sitepilot/app/public
ENV SITEPILOT_ROOT=/opt/sitepilot/app/.sitepilot
ENV DEPLOY_TOKEN="jrCgCa9AFzqNMlvMWkGQ5ozOdrqdjt0I"

# ----- Build Files ----- #

COPY build /

# ----- Common ----- #

RUN install-packages software-properties-common supervisor curl wget gpg-agent unzip mysql-client git

# ----- Dockerize ----- #

RUN wget https://github.com/presslabs/dockerize/releases/download/v$DOCKERIZE_VERSION/dockerize-linux-amd64-v$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-v$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-v$DOCKERIZE_VERSION.tar.gz

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
    && php composer-setup.php \
    && mv composer.phar /usr/local/bin/composer \
    && php -r "unlink('composer-setup.php');" \
    && composer --version

# ----- Webhook ----- #

RUN install-packages webhook

# ----- Files ----- #

COPY filesystem /

RUN mkdir /var/www \
    && mkdir /opt/sitepilot/app \
    && mkdir -p /var/lib/nginx/logs \
    && chown -R www-data:www-data /run \
    && chown -R www-data:www-data /opt/sitepilot \
    && chown -R www-data:www-data /var/lib/nginx \
    && chown -R www-data:www-data /var/www
    
RUN ln -sf /opt/sitepilot/etc/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/zz-01-custom.ini \
    && ln -sf ${SITEPILOT_ROOT}}/config/php/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/zz-90-app.ini

# ----- Config ----- #

USER www-data:www-data 

WORKDIR /opt/sitepilot/app

ENTRYPOINT ["/opt/sitepilot/bin/entrypoint"]

CMD ["supervisord", "-c", "/opt/sitepilot/etc/supervisor.conf"]
