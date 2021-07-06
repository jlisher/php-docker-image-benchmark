FROM docker.io/library/php:7-fpm

# fixes some weird terminal issues such as broken clear / CTRL+L
ENV TERM=linux

# ensure apt doesn't ask questions when installing stuff
ENV DEBIAN_FRONTEND=noninteractive

# install extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# install updates and mysqli
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get -y --no-install-recommends install libfcgi-bin \
    && install-php-extensions mysqli \
    && apt-get -y clean \
    && apt-get -y autoclean \
    && apt-get -y autoremove \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# install composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# PHP-FPM packages need a nudge to make them docker-friendly
COPY overrides.conf /etc/php/7.4/fpm/pool.d/z-overrides.conf

# install health check
RUN curl -L -o "/usr/local/bin/php-fpm-healthcheck" "https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck" \
    && chmod +x "/usr/local/bin/php-fpm-healthcheck"
HEALTHCHECK CMD /bin/sh /usr/local/bin/php-fpm-healthcheck

# set stop signal to send
STOPSIGNAL SIGQUIT

# open up fcgi port
EXPOSE 9000

# set hostname
ENV HOSTNAME official

# copy our benchmarcking app code
RUN mkdir -p /app
COPY ./app /app/official
WORKDIR /app/official
