FROM php:7.1-apache

RUN apt-get update \
# @exclude
    && curl -sL https://deb.nodesource.com/setup_4.x | bash - \
# @endexclude
    && apt-get install -y --no-install-recommends \
      libicu-dev \
      libpq-dev \
      libxml2-dev \
      ca-certificates \
      ssl-cert \
      libcurl4-gnutls-dev \
      git \
      unzip \
      bzip2 \
      netcat-openbsd \
      libfontconfig1 \
# @exclude
      nodejs \
# @endexclude
    && update-ca-certificates \
    && docker-php-ext-install \
      pdo_pgsql \
      pgsql \
      opcache \
      curl \
      bcmath \
      zip \
      soap \
    && apt-get autoremove \
    && rm -r /var/lib/apt/lists/*

RUN usermod --uid 1001 www-data \
	&& groupmod --gid 1001 www-data \
	&& chown -hR www-data:www-data /var/www

COPY php.ini /usr/local/etc/php/php.ini
COPY apache2-sites/*.conf /etc/apache2/sites-available/
COPY apache2-ssl /etc/apache2/ssl

RUN a2enmod ssl rewrite proxy_http expires deflate && a2ensite default-ssl

ENV REPEKA_ENV prod
