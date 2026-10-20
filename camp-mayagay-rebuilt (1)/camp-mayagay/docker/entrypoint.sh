#!/bin/sh
set -e

# Wait for MySQL (compose also has a healthcheck, this is a safety net)
i=0
until php -r 'require "/var/www/html/php/db.php"; echo "DB_OK";' 2>/dev/null | grep -q DB_OK; do
  i=$((i+1))
  if [ "$i" -ge 40 ]; then echo "MySQL not reachable, giving up"; exit 1; fi
  echo "Waiting for MySQL... ($i)"
  sleep 2
done

php /var/www/html/php/seed.php

# Uploaded files live in a named volume - make sure Apache can write to it
mkdir -p /var/www/html/assets/uploads/avatars /var/www/html/assets/uploads/valid_ids /var/www/html/assets/uploads/accommodations
chown -R www-data:www-data /var/www/html/assets/uploads

exec apache2-foreground
