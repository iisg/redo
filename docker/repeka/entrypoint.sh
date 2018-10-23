#!/bin/sh

if [ ! -f /etc/apache2/ssl/server.crt ]; then
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/server.key -out /etc/apache2/ssl/server.crt -subj "/C=PL/ST=REPEKA/L=REPEKA/O=REPEKA/CN=REPEKA"
fi

rm -fr /var/www/html/var/cache/*
/usr/local/bin/php /var/www/html/bin/console repeka:initialize --no-interaction
/usr/local/bin/php /var/www/html/bin/console cache:warmup -e prod --no-interaction
chown -R www-data:www-data var/backups var/cache var/config var/import var/logs var/ssl var/uploads

exec "$@"
