#!/bin/bash
REPO=~/placko.app

cd $REPO
echo "→ git pull..."
git pull origin main

echo "→ composer install..."
composer install --no-dev --optimize-autoloader

php $REPO/artisan migrate --force
php $REPO/artisan optimize

echo "✓ Deployed!"
