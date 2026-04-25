FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    nodejs npm zip unzip git curl git-lfs \
    libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && git lfs install

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer



COPY biblioteka/ /var/www/html/

WORKDIR /var/www/html

RUN git lfs pull



RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

RUN npm install && npm run build

EXPOSE 8000

CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000