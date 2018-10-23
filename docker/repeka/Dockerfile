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
     supervisor \
     cron \
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

COPY crontab /etc/cron.d/repeka

RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=4000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini \
    && a2enmod ssl rewrite proxy_http expires deflate && a2ensite default-ssl \
    && chmod 0644 /etc/cron.d/repeka

ARG WWW_DATA_UID=1001
RUN usermod --uid "$WWW_DATA_UID" www-data \
  && groupmod --gid "$WWW_DATA_UID" www-data \
  && chown -hR www-data:www-data /var/www

COPY php.ini /usr/local/etc/php/php.ini
COPY apache2-sites/*.conf /etc/apache2/sites-available/

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY entrypoint.sh /usr/local/bin/docker-php-entrypoint

ENV REPEKA_ENV prod

CMD ["/usr/bin/supervisord", "--nodaemon", "--configuration", "/etc/supervisor/conf.d/supervisord.conf", "--logfile", "/var/www/html/var/logs/supervisor.log", "--pidfile", "/supervisor.pid"]
