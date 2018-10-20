FROM php:7.1.16-apache

RUN curl -sL https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
  && echo "deb http://apt.postgresql.org/pub/repos/apt/ jessie-pgdg main" >> /etc/apt/sources.list.d/pgdg.list \
  && apt-get update \
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
     zlib1g-dev \
     postgresql-client-10 \
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
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean

# @exclude
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
  && apt-get install -y --no-install-recommends nodejs \
  && apt-get autoremove \
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean \
  && npm install -g npm@5
# @endexclude

ARG WWW_DATA_UID=1001
RUN usermod --uid "$WWW_DATA_UID" www-data \
  && groupmod --gid "$WWW_DATA_UID" www-data \
  && chown -hR www-data:www-data /var/www

COPY php.ini /usr/local/etc/php/php.ini
COPY apache2-sites/*.conf /etc/apache2/sites-available/

RUN a2enmod ssl rewrite proxy_http expires deflate && a2ensite default-ssl

ENV REPEKA_ENV prod
