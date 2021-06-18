FROM docker.io/phpdockerio/php74-fpm:latest

# fixes some weird terminal issues such as broken clear / CTRL+L
ENV TERM=linux

# ensure apt doesn't ask questions when installing stuff
ENV DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install php7.4-mysql \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# install health check
RUN curl -L -o "/usr/local/bin/php-fpm-healthcheck" "https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck" \
    && chmod +x "/usr/local/bin/php-fpm-healthcheck"
HEALTHCHECK --interval=1m --timeout=5s --start-period=5s \
    CMD /bin/sh /usr/local/bin/php-fpm-healthcheck

# set hostname
ENV HOSTNAME phpdockerio

# copy our benchmarcking app code
RUN mkdir -p /app
COPY ./app /app/phpdockerio
WORKDIR /app/phpdockerio
