FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        libpq-dev \
        libzip-dev \
        make \
        unzip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin --filename=composer \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && groupadd --system --gid 1001 app \
    && useradd --system --uid 1001 --gid app --home-dir /home/app --create-home app

WORKDIR /app

COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

COPY config ./config
COPY public ./public
COPY src ./src
COPY templates ./templates
COPY Makefile database.sql ./

RUN chown -R app:app /app

USER app

CMD ["bash", "-c", "make start"]
