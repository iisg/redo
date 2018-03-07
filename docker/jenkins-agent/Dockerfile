FROM php:7.1-apache

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
     wget \
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

# Install XDebug
RUN pecl channel-update pecl.php.net \
	&& pecl install xdebug \
	&& docker-php-ext-enable xdebug

# Install Xvfb for frontend tests
RUN echo "deb http://dl.google.com/linux/chrome/deb/ stable main" | tee -a /etc/apt/sources.list
RUN wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
RUN apt-get update \
        && apt-get install -y libxpm4 libxrender1 libgtk2.0-0 libnss3 libgconf-2-4 \
	&& apt-get install -y google-chrome-stable \
	&& apt-get install -y xvfb gtk2-engines-pixbuf \
	&& apt-get install -y xfonts-cyrillic xfonts-100dpi xfonts-75dpi xfonts-base xfonts-scalable \
	&& apt-get install -y imagemagick x11-apps

## Install Node.js (as requested by user)
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash - \
  && apt-get install -y --no-install-recommends nodejs \
  && apt-get autoremove \
  && rm -r /var/lib/apt/lists/* \
  && apt-get clean

RUN usermod --uid 1001 www-data \
	&& groupmod --gid 1001 www-data \
	&& chown -hR www-data:www-data /var/www/html
VOLUME /var/www/html

COPY php.ini /usr/local/etc/php/php.ini
COPY apache2-sites/*.conf /etc/apache2/sites-available/
COPY apache2-ssl /etc/apache2/ssl

# Here goes rip-off of jenkinsci/jnlp-slave Dockerfile
COPY tini /sbin/tini

RUN echo "deb http://http.debian.net/debian jessie-backports main" > /etc/apt/sources.list.d/jessie-backports.list \
        && apt-get update \
	&& apt-get install -y -t jessie-backports openjdk-8-jre-headless ca-certificates-java git openssl openssh-client \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*

ENV HOME /home/jenkins
RUN useradd -c "Jenkins user" -d $HOME -m jenkins

ARG JENKINS_REMOTING_VERSION=3.7

RUN curl --create-dirs -sSLo /usr/share/jenkins/slave.jar https://repo.jenkins-ci.org/public/org/jenkins-ci/main/remoting/${JENKINS_REMOTING_VERSION}/remoting-${JENKINS_REMOTING_VERSION}.jar \
	&& chmod 755 /usr/share/jenkins \
	&& chmod 644 /usr/share/jenkins/slave.jar

COPY jenkins-slave /usr/local/bin/jenkins-slave

VOLUME /home/jenkins
WORKDIR /home/jenkins
USER jenkins

ENTRYPOINT ["/sbin/tini", "--", "jenkins-slave"]
