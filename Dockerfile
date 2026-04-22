FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    nodejs npm zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
   
    rm -f /etc/apache2/mods-available/mpm_*.load && \
    rm -f /etc/apache2/mods-available/mpm_*.conf && \
    rm -f /etc/apache2/mods-enabled/mpm_*.load && \
    rm -f /etc/apache2/mods-enabled/mpm_*.conf && \
    
    a2enmod mpm_prefork && \
    a2enmod rewrite

COPY biblioteka/ /var/www/html/

WORKDIR /var/www/html


RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN npm install && npm run build

EXPOSE 80

CMD apache2-foreground