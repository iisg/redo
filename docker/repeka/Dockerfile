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
     exif \
  && apt-get autoremove \
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean

RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get autoremove \
    && rm -r /var/lib/apt/lists/*

# @exclude
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash - \
  && apt-get install -y --no-install-recommends nodejs \
  && apt-get autoremove \
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean \
  && npm install -g npm@5
# @endexclude


RUN apt-get update && apt-get install -y \
        libfontconfig1 \
        libxrender1 \
        libxext6 \
    && curl -L -o wkhtmltox.tar.xz https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz \
    && tar xf wkhtmltox.tar.xz -C / \
    && rm -f wkhtmltox.tar.xz \
    && apt-get autoremove \
    && rm -r /var/lib/apt/lists/* \
    && apt-get clean

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
