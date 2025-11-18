FROM php:8.2-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

WORKDIR /var/www/html

# System dependencies + PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libicu-dev \
    libonig-dev \
    libjpeg-dev \
    libfreetype6-dev \
    supervisor \
    gnupg \
    dos2unix \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_pgsql bcmath intl gd pcntl zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Node + npm for Vite builds (if you build inside container)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && npm install -g npm@latest \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# config
COPY ./docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/startup.sh /usr/local/bin/startup.sh
COPY ./docker/supervisord.conf /etc/supervisor/supervisord.conf

# Convert line endings to Unix format, fix for windows
RUN dos2unix /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

# Ensure runtime directories exist and have correct perms
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/logs/supervisor \
    && chown -R www-data:www-data /var/www/html

# We keep USER as root so supervisor + chown work;
# php-fpm itself still runs worker processes as www-data.
# USER root

ENTRYPOINT ["/usr/local/bin/startup.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
