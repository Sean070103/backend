# Laravel on Render: PHP 8.4 + artisan serve (listens on PORT)
FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libpq-dev \
    && docker-php-ext-install zip pdo_mysql pdo_pgsql mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./
# Ignore platform reqs so Docker build doesn't fail on PHP/ext checks from lock file
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --ignore-platform-reqs

# Create storage/cache dirs and ensure writable
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

EXPOSE 8000

CMD ["/render-start.sh"]
