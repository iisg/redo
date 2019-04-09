FROM php:7.1.27-apache-stretch

RUN apt-get update && apt-get -y install gnupg2 \
  && curl -sL https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add - \
  && echo 'deb http://apt.postgresql.org/pub/repos/apt/ stretch-pgdg main' > /etc/apt/sources.list.d/pgdg.list \
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
     curl \
     soap \
  && apt-get autoremove \
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean
