#!/bin/sh

# Ensure all necessary services are up
echo "Starting Supervisor..."

# Start Supervisor
# /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &

# Wait a bit for Supervisor to start
sleep 5

# Start Yii Queue Listener
echo "Starting Yii queue listener..."
php yii queue/listen

# Keep container running
exec "$@"
