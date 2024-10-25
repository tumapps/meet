 FROM vaultke/php8-fpm-nginx

# Install packages
RUN apk update && apk add --no-cache \
    supervisor \
    busybox-suid \
    bash \
    tini \
    && rm -rf /var/cache/apk/*

COPY . /var/www/html/omnibase

WORKDIR /var/www/html/omnibase

# RUN composer install

RUN php yii voyage

# RUN chmod -R 777 /var/www/html/omnibase

# Supervisor configuration directory
RUN mkdir -p /etc/supervisor/conf.d/

COPY supervisor/email_queue.conf /etc/supervisor/conf.d/email_queue.conf
COPY supervisor/email_worker.conf /etc/supervisor/conf.d/email_worker.conf


# Use tini to start supervisord in the foreground
# ENTRYPOINT ["/sbin/tini", "--"]

# CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

