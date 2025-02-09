#!/bin/sh

# Create logs directory if it doesn't exist
mkdir -p /var/www/html/storage/logs

# Ensure the RabbitMQ log file exists
touch /var/www/html/storage/logs/rabbitmq.log
chmod -R 777 /var/www/html/storage/logs

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
