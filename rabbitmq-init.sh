#!/bin/sh

# Start RabbitMQ in the background
rabbitmq-server &

# Wait for RabbitMQ to fully start
sleep 10

# Create the virtual host 'dev'
rabbitmqctl add_vhost dev

# Set permissions for the virtual host
rabbitmqctl set_permissions -p dev guest ".*" ".*" ".*"

# Bring RabbitMQ to the foreground
wait
