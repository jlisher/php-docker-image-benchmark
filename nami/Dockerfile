FROM docker.io/bitnami/php-fpm:7.4-prod

# PHP-FPM packages need a nudge to make them docker-friendly
COPY overrides.conf /etc/php/7.4/fpm/pool.d/z-overrides.conf

# install health check
RUN curl -L -o "/usr/local/bin/php-fpm-healthcheck" "https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck" \
    && chmod +x "/usr/local/bin/php-fpm-healthcheck"
HEALTHCHECK --interval=1m --timeout=5s --start-period=5s \
    CMD /bin/sh /usr/local/bin/php-fpm-healthcheck

# set hostname
ENV HOSTNAME nami

# copy our benchmarcking app code
RUN mkdir -p /app
COPY ./app /app/nami
WORKDIR /app/nami
