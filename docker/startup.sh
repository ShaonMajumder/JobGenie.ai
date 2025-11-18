#!/bin/bash

export COMPOSER_CACHE_DIR=/composer/cache

cd /var/www/html

# Ensure environment directory exists
if [ ! -d "docker/environment" ]; then
    echo "Error: docker/environment/ directory not found."
    exit 1
fi

# If artisan not found, assume Laravel not present (fallback)
if [ ! -f artisan ]; then
    echo "Laravel not found. Creating Laravel in /tmp/laravel..."
    composer create-project --prefer-dist laravel/laravel:^12.0 /tmp/laravel
    if [ $? -ne 0 ]; then
        echo "Error: Failed to create Laravel project"
        exit 1
    fi

    # Copy the environment file if specified
    if [ -n "$APP_ENV_FILE" ]; then
        echo "Copying $APP_ENV_FILE from docker/environment directory..."
        cp docker/environment/$APP_ENV_FILE /tmp/laravel/.env
    else
        echo "No APP_ENV_FILE specified, using existing .env.local"
        echo "Copying .env.local from docker/environment directory..."
        cp docker/environment/.env.local /tmp/laravel/.env
    fi

    # Clear config in the newly created project
    # cd /tmp/laravel
    # php artisan config:clear
    # cd /var/www/html

    echo "Copying Laravel to current directory..."
    cp -R /tmp/laravel/. .
    rm -rf /tmp/laravel
else
    echo "Laravel already exists. Skipping creation."
    if [ -n "$APP_ENV_FILE" ]; then
        echo "Copying $APP_ENV_FILE from docker/environment directory..."
        cp docker/environment/$APP_ENV_FILE ./.env
    else
        echo "No APP_ENV_FILE specified, using existing .env.local"
        echo "Copying .env.local from docker/environment directory..."
        cp docker/environment/.env.local ./.env
    fi
fi



echo "Setting correct permissions for the storage directory..."
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
# chown -R www-data:www-data /var/www/html/storage
# chmod -R 775 /var/www/html/storage

echo "Ensuring logs/supervisor directory exists..."
mkdir -p /var/www/html/logs/supervisor
chown -R www-data:www-data /var/www/html/logs


############################################
# 🔥 Frontend: npm install + Vite build
############################################

if [ -f "package.json" ]; then
    echo "package.json found."

    # 1) Install node_modules if missing
    if [ ! -d "node_modules" ] || [ -z "$(ls -A node_modules 2>/dev/null || echo '')" ]; then
        echo "node_modules missing. Running npm install..."
        npm install --no-fund --no-audit
    else
        echo "node_modules already exists. Skipping npm install."
    fi

    # 2) Build assets if manifest is missing
    if [ ! -f "public/build/manifest.json" ]; then
        echo "Vite manifest not found. Running npm run build..."
        npm run build
    else
        echo "Vite manifest already exists. Skipping npm run build."
    fi
else
    echo "No package.json found, skipping frontend build."
fi

echo "Clearing and caching Laravel config/routes/views..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Startup script completed. Handing over to main process..."
exec "$@"