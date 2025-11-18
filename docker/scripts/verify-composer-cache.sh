#!/bin/bash

echo "Checking COMPOSER_CACHE_DIR environment variable..."
if [ -z "$COMPOSER_CACHE_DIR" ]; then
    echo "❌ COMPOSER_CACHE_DIR is not set."
    CACHE_DIR=$(composer config cache-dir 2>/dev/null)
else
    echo "✅ COMPOSER_CACHE_DIR is set to: $COMPOSER_CACHE_DIR"
    CACHE_DIR="$COMPOSER_CACHE_DIR"
fi

echo "Checking Composer cache-dir config..."
composer_cache_config=$(composer config cache-dir 2>/dev/null)
echo "Composer cache-dir: $composer_cache_config"

if [ "$CACHE_DIR" != "$composer_cache_config" ]; then
    echo "❌ Composer is not using the expected cache directory."
else
    echo "✅ Composer is using the expected cache directory."
fi

echo "Checking if cache directory exists and has content..."
if [ -d "$composer_cache_config" ] && [ "$(ls -A "$composer_cache_config")" ]; then
    echo "✅ Composer cache directory exists and is not empty."
else
    echo "⚠️ Composer cache directory does not exist or is empty."
fi