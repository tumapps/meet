FROM vaultke/php8.3-fpm-nginx

COPY . /var/www/html

# Copy Nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY cronjob.conf /etc/cron.d/app-cron

RUN chmod 0644 /etc/cron.d/app-cron && crontab /etc/cron.d/app-cron

# Copy the RabbitMQ Supervisor configuration file
COPY yii2-rabbitmq-queue.ini /etc/supervisor.d/yii2-rabbitmq-queue.ini

WORKDIR /var/www/html

RUN chmod -R 777 /var/www/html