FROM vaultke/php8.3-fpm-nginx

COPY . /var/www/html
# COPY supervisor/email_queue.conf /etc/supervisor/conf.d/email_queue.conf
# COPY supervisor/email_worker.conf /etc/supervisor/conf.d/email_worker.conf
COPY default.conf /etc/nginx/conf.d/default.conf
COPY cronjob.conf /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

WORKDIR /var/www/html

COPY supervisord.conf /etc/supervisor/supervisord.conf

COPY ./supervisor/email_queue.conf /etc/supervisor/conf.d/email_queue.conf

# Set Supervisor as the container's main process
# CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]


RUN chmod -R 777 /var/www/html