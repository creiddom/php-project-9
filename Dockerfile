FROM php:8.4-cli

RUN apt-get update && apt-get install -y libzip-dev libpq-dev make \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');" \
    && groupadd --system --gid 1001 app \
    && useradd --system --uid 1001 --gid app --home-dir /app app

WORKDIR /app

COPY --chown=app:app composer.json composer.lock ./
COPY --chown=app:app Makefile ./
COPY --chown=app:app public ./public
COPY --chown=app:app src ./src
COPY --chown=app:app templates ./templates

USER app

RUN composer install --no-dev --optimize-autoloader

CMD ["bash", "-c", "make start"]
