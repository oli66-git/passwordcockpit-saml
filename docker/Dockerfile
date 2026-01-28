FROM --platform=linux/amd64 php:8.2-apache-bullseye

# Install PHP extensions and system dependencies
RUN set -ex; \
    apt-get update; \
    # Install runtime dependencies (will NOT be removed)
    apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip4 \
        libicu67 \
        libldap-2.4-2; \
    # Install build dependencies (will be removed after build)
    apt-get install -y --no-install-recommends \
        libzip-dev \
        libicu-dev \
        libldap2-dev \
        curl; \
    # Install PHP extensions
    docker-php-ext-install \
        opcache \
        zip \
        intl \
        ldap \
        pdo_mysql; \
    # Enable Apache modules
    a2enmod rewrite; \
    a2enmod headers; \
    # Install Composer
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer; \
    # Install xdebug
    pecl install xdebug; \
    # Cleanup build dependencies only (keep runtime libs)
    apt-get purge -y --auto-remove \
        libzip-dev \
        libicu-dev \
        libldap2-dev; \
    rm -rf /var/lib/apt/lists/*

# Prepare /var/www/html
RUN mkdir -p /var/www/html && \
    chown -R www-data:www-data /var/www/html

# Copy source code directly to /var/www/html
COPY . /var/www/html

# Build in /var/www/html
WORKDIR /var/www/html

# Fix git safe.directory warning
RUN git config --global --add safe.directory /var/www/html

# Install PHP dependencies via Composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --prefer-dist --optimize-autoloader

# Generate Swagger/OpenAPI documentation
RUN {  echo "<?php"; \
    echo "define('SWAGGER_API_HOST', 'PASSWORDCOCKPIT_BASEHOST');"; } > config/constants.local.php

RUN composer swagger

# Configure Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set timezone
RUN rm -rf /etc/localtime && ln -s /usr/share/zoneinfo/Europe/Berlin /etc/localtime

# Security headers
RUN sed -ri -e 's!ServerTokens OS!ServerTokens Prod!g' /etc/apache2/conf-enabled/security.conf
RUN sed -ri -e 's!ServerSignature On!ServerSignature Off!g' /etc/apache2/conf-enabled/security.conf

# PHP configuration
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# Disable xdebug to prevent output before headers
RUN { \
    echo '[xdebug]'; \
    echo 'zend_extension=xdebug.so'; \
    echo 'xdebug.mode=off'; \
    } > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN sed -i \
    -e 's~^;date.timezone =$~date.timezone = Europe/Zurich~g' \
    -e 's~^display_errors = Off$~display_errors = On~g' \
    -e 's~^display_startup_errors = Off$~display_startup_errors = On~g' \
    -e 's~^memory_limit = .*$~memory_limit = 256M~g' \
    -e 's~^post_max_size = .*$~post_max_size = 128M~g' \
    -e 's~^max_execution_time = .*$~max_execution_time = 60~g' \
    -e 's~^output_buffering = .*$~output_buffering = 4096~g' \
    /usr/local/etc/php/php.ini

# OPcache configuration
RUN {  \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=60'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Enable development mode (skip in Docker build, will be done in entrypoint)
# RUN composer development-enable

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

VOLUME /var/www/html
WORKDIR /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["apache2-foreground"]
