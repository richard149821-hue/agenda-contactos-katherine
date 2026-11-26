FROM php:8.3-apache
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mysqli mbstring && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
RUN a2enmod headers && chown -R www-data:www-data /var/www/html
EXPOSE 80
