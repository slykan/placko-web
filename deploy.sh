#!/bin/bash
REPO=~/placko.app
# PHP 8.4 kao web (Hestia vps2: sistemski php je 8.5)
PHP=/usr/bin/php8.4
[ -x "$PHP" ] || PHP=php

cd $REPO
echo "→ git pull..."
git pull origin main

echo "→ composer install..."
$PHP $(command -v composer) install --no-dev --optimize-autoloader

$PHP $REPO/artisan migrate --force
[ -L $REPO/public/storage ] || $PHP $REPO/artisan storage:link
$PHP $REPO/artisan optimize:clear
$PHP $REPO/artisan optimize

echo "✓ Deployed!"
