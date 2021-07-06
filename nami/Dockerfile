FROM docker.io/bitnami/php-fpm:7.4-prod

# PHP-FPM packages need a nudge to make them docker-friendly
COPY overrides.conf /etc/php/7.4/fpm/pool.d/z-overrides.conf

# install health check
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get -y --no-install-recommends install libfcgi-bin \
    && apt-get -y clean \
    && apt-get -y autoclean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
    && curl -L -o "/usr/local/bin/php-fpm-healthcheck" "https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck" \
    && chmod +x "/usr/local/bin/php-fpm-healthcheck"
HEALTHCHECK CMD /usr/local/bin/php-fpm-healthcheck

# set hostname
ENV HOSTNAME nami

# copy our benchmarcking app code
RUN mkdir -p /app
COPY ./app /app/nami
WORKDIR /app/nami
