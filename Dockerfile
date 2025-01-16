 FROM vaultke/php8-fpm-nginx

# Install packages
RUN apk update && apk add --no-cache \
    supervisor \
    bash \
    nano \
    && rm -rf /var/cache/apk/*

RUN export EDITOR=nano

# Supervisor configuration directory
RUN mkdir -p /etc/supervisor/conf.d/

COPY . /var/www/html
# COPY supervisor/email_queue.conf /etc/supervisor/conf.d/email_queue.conf
# COPY supervisor/email_worker.conf /etc/supervisor/conf.d/email_worker.conf
COPY default.conf /etc/nginx/conf.d/default.conf
COPY cronjob.conf /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

WORKDIR /var/www/html

# RUN chmod -R 777 /var/www/html




