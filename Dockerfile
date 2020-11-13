FROM ubuntu:20.04
LABEL maintainer "Sitepilot <support@sitepilot.io>"

ENV PHP_VERSION=7.4
ENV PATH="/opt/sitepilot/bin:${PATH}"

ENV APP_NAME="undefined"
ENV APP_PATH=/opt/sitepilot/app
ENV APP_PATH_PUBLIC=/opt/sitepilot/app/public
ENV APP_PATH_DEPLOY=/opt/sitepilot/app/deploy
ENV APP_PATH_LOGS=/opt/sitepilot/app/logs
ENV APP_PATH_AUTH=/opt/sitepilot/app/.auth
ENV COMPOSER_HOME=/opt/sitepilot/app/.composer

# ----- Build Files ----- #

COPY build /

# ----- Packages ----- #

RUN install-packages sudo software-properties-common supervisor curl wget gpg-agent unzip mysql-client git ssh msmtp nano openssh-server zsh nginx

# ----- PHP ----- #

RUN add-apt-repository ppa:ondrej/php -y \
    && install-packages php${PHP_VERSION}-fpm php${PHP_VERSION}-common php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-xml php${PHP_VERSION}-xmlrpc php${PHP_VERSION}-curl php${PHP_VERSION}-gd \
    php${PHP_VERSION}-imagick php${PHP_VERSION}-cli php${PHP_VERSION}-dev php${PHP_VERSION}-imap \
    php${PHP_VERSION}-mbstring php${PHP_VERSION}-opcache php${PHP_VERSION}-redis \
    php${PHP_VERSION}-soap php${PHP_VERSION}-zip php${PHP_VERSION}-intl \
    && mkdir -p /run/php \
    && php-fpm${PHP_VERSION} -v

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
    && usermod -u 10000 -d /opt/sitepilot/app www-data \
    && groupmod -g 10000 www-data \
    && chsh -s /bin/zsh www-data \
    && git clone --depth=1 --branch=master https://github.com/ohmyzsh/ohmyzsh.git /opt/sitepilot/ohmyzsh \
    && rm -rf /opt/sitepilot/ohmyzsh/.git \
    && chmod +x /opt/sitepilot/ohmyzsh/oh-my-zsh.sh
    
# ----- Files ----- #

COPY filesystem /

RUN mkdir -p /opt/sitepilot/etc \
    && mkdir -p /var/log/nginx \
    && mkdir -p /var/lib/nginx \
    && mkdir -p /var/run/php \
    && chown -R www-data:www-data /run \
    && chown -R www-data:www-data /opt/sitepilot \
    && chown -R www-data:www-data /var/log/nginx \
    && chown -R www-data:www-data /var/lib/nginx \
    && rm -rf /etc/nginx \
    && ln -s /opt/sitepilot/etc/nginx /etc/nginx \
    && ln -sf /opt/sitepilot/etc/php/php.ini /etc/php/${PHP_VERSION}/fpm/conf.d/99-sitepilot.ini

# ----- Config ----- #

EXPOSE 8080

USER 10000:10000

WORKDIR /opt/sitepilot/app

ENTRYPOINT ["/opt/sitepilot/bin/entrypoint"]

CMD ["supervisord", "-c", "/opt/sitepilot/etc/supervisor/supervisor.conf"]
