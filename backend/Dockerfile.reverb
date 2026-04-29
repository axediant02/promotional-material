FROM php:8.2-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip zip libzip-dev \
    && docker-php-ext-install pcntl pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

EXPOSE 8080

CMD ["sh", "-c", "php artisan reverb:start --host=0.0.0.0 --port=${PORT:-8080}"]
