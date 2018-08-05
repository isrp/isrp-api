FROM php:zts-alpine

RUN apk add --no-cache git
RUN curl -sf https://getcomposer.org/download/1.7.0/composer.phar -o /usr/bin/composer && chmod 755 /usr/bin/composer
RUN adduser -u 3000 -G www-data -S -h /app isrp
USER isrp
WORKDIR /app
EXPOSE 1280
ADD composer.* /app/
RUN composer -n --no-ansi install --no-dev -o #1
ADD src /app/src
CMD /app/src/index.php
