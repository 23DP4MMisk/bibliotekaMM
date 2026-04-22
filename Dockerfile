FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    nodejs npm zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

COPY biblioteka/ /var/www/html/

WORKDIR /var/www/html


RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN npm install && npm run build

EXPOSE 80

CMD apache2-foreground