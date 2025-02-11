FROM vaultke/php8.3-fpm-nginx

COPY . /var/www/html
COPY cronjob.conf /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

WORKDIR /var/www/html

RUN chmod -R 777 /var/www/html