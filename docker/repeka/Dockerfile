FROM php:7.1-apache

# Prepare environment
#   - enable backports repository (as it contains OpenSSL 1.0.2)
#   - enable testing repository (as it contains Apache 2.4 with HTTP/2)
#   - install packages from testing only on demand (pin 100)
#   - upgrade OpenSSL to 1.0.2 (HTTP/2 requires ALPN-capable version)
#   - upgrade Apache to newer version (with mod_http2 available)
#   - enable ssl and http2 modules in Apache
#   - activate default SSL vhost in Apache
RUN echo 'deb http://httpredir.debian.org/debian jessie-backports main' > /etc/apt/sources.list.d/jessie-backports.list \
	&& echo 'deb http://httpredir.debian.org/debian stretch main' > /etc/apt/sources.list.d/testing.list \
	&& echo 'Package: *\nPin: release a=testing\nPin-Priority: 100' > /etc/apt/preferences.d/testing-pin-100 \
	&& apt-get update \
	&& apt-get install -y -t jessie-backports \
		openssl \
	&& apt-get install -y -t testing -o Dpkg::Options::='--force-confnew' apache2 \
	&& a2enmod \
		ssl \
		http2 \
	&& a2ensite default-ssl \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions for databases
RUN apt-get update \
	&& apt-get install -y -t jessie-backports \
		libmysqlclient-dev \
		libpq-dev \
		libxml2-dev \
	&& docker-php-ext-install \
		mysqli \
		pdo_mysql \
		pgsql \
		pdo_pgsql \
		bcmath \
		zip \
		soap \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

## Install Node.js (as requested by user)
RUN curl -sL https://deb.nodesource.com/setup_4.x | bash - \
	&& apt-get install -y nodejs \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

RUN usermod --uid 1001 www-data \
	&& groupmod --gid 1001 www-data \
	&& chown -hR www-data:www-data /var/www
VOLUME /var/www/html

## Install other required tools
RUN apt-get update \
	&& apt-get install -y git \
		unzip \
		bzip2 \
		netcat-openbsd \
		libfontconfig1 \
	&& apt-get clean \
        && rm -rf /var/lib/apt/lists/*

COPY php.ini /usr/local/etc/php/php.ini
COPY apache2-sites/*.conf /etc/apache2/sites-available/
COPY apache2-ssl /etc/apache2/ssl

RUN a2enmod rewrite proxy_http
RUN kill -USR1 1

ENV REPEKA_ENV prod
