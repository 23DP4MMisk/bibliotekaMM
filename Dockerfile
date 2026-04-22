FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    nodejs npm zip unzip git curl \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN a2enmod rewrite

COPY . /var/www/html/

# ЭТИ СТРОКИ УБРАНЫ (будут созданы автоматически при запуске)
# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 755 /var/www/html/storage \
#     && chmod -R 755 /var/www/html/bootstrap/cache

RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm install && npm run build

EXPOSE 80

CMD php artisan migrate --force && php artisan storage:link && apache2-foreground