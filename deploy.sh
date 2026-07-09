#!/bin/bash
REPO=~/placko.app

cd $REPO
echo "→ git pull..."
git pull origin main

echo "→ composer install..."
composer install --no-dev --optimize-autoloader

php $REPO/artisan migrate --force
[ -L $REPO/public/storage ] || php $REPO/artisan storage:link
php $REPO/artisan optimize

echo "✓ Deployed!"
