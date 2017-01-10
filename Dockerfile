FROM fabschurt/php7-fpm
MAINTAINER Fabien Schurter <fabien@fabschurt.com>

ARG ENVIRONMENT=prod

COPY . /opt/codebase
WORKDIR /opt/codebase
RUN apk update --no-cache && \
    apk add \
      bash \
      git  \
      curl \
    && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    ./bin/build backend && \
    chgrp -R php-fpm app src var vendor web && \
    chmod -R g+r app src var vendor web && \
    chmod -R g+w var && \
    apk del --purge \
      bash \
      git  \
      curl \
    && \
    rm -rf /var/cache/apk/*        \
           /root/.composer/cache/* \
           /usr/local/bin/composer
