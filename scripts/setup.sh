#!/usr/bin/env bash
set -euo pipefail

echo "Starting setup script for indexer feature"

if [ ! -f composer.json ]; then
  echo "composer.json not found. Are you in the project root?"
  exit 1
fi

echo "Installing composer dependencies..."
composer install --no-interaction

if [ -f artisan ]; then
  echo "Generating app key..."
  php artisan key:generate

  echo "Running migrations..."
  php artisan migrate --force

  echo "Seeding scan source example..."
  php artisan db:seed --class=\"Database\\Seeders\\ScanSourceSeeder\" --force

  echo "Creating storage directories and setting permissions..."
  mkdir -p storage/app/private
  mkdir -p storage/app/indexer
  chmod -R 0775 storage
  chown -R www-data:www-data storage || true

  echo "Setup complete. You can run the indexer via: php artisan indexer:scan --source=local-samples"
else
  echo "artisan file not found. Ensure this is a Laravel application root."
  exit 1
fi
