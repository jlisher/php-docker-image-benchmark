FROM docker.io/library/debian:10-slim

# fixes some weird terminal issues such as broken clear / CTRL+L
ENV TERM=linux

# ensure apt doesn't ask questions when installing stuff
ENV DEBIAN_FRONTEND=noninteractive

# install Ondrej repos for PHP7.4 and selected extensions - better selection than
# the distro's packages
RUN apt-get update \
    && apt-get upgrade -y \
    && apt-get -y --no-install-recommends install apt-transport-https lsb-release ca-certificates curl \
    && curl -L -o /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get -y --no-install-recommends install \
        ca-certificates \
        curl \
        libfcgi-bin \
        unzip \
        php7.4-apcu \
        php7.4-apcu-bc \
        php7.4-cli \
        php7.4-curl \
        php7.4-fpm \
        php7.4-json \
        php7.4-mbstring \
        php7.4-mysql \
        php7.4-opcache \
        php7.4-readline \
        php7.4-xml \
        php7.4-zip \
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

# set a good default starting script
CMD ["/usr/sbin/php-fpm7.4", "-O"]

# set hostname
ENV HOSTNAME debian

# copy our benchmarcking app code
RUN mkdir -p /app
COPY ./app /app/debian
WORKDIR /app/debian
