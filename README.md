# Анализатор страниц

Веб-приложение для добавления сайтов и периодической проверки их доступности и базовых SEO-показателей. Учебный проект [Hexlet](https://ru.hexlet.io) по PHP.

## Описание

На главной странице можно указать URL сайта и добавить его в базу. При сохранении адрес нормализуется до схемы и хоста (например, `https://example.com/page` → `https://example.com`). Для каждого сайта доступна страница с историей проверок.

По кнопке «Запустить проверку» приложение запрашивает сайт по HTTP, сохраняет код ответа и извлекает из HTML заголовок `h1`, тег `title` и мета-описание `description`. Список всех сайтов и код последней проверки отображаются на странице `/urls`.

## Возможности

- добавление URL с валидацией и сообщениями об ошибках;
- защита от дубликатов (один и тот же хост не добавляется повторно);
- HTTP-проверка сайта с сохранением результата в PostgreSQL;
- просмотр истории проверок с обрезкой длинных полей в интерфейсе;
- флеш-сообщения об успехе и ошибках.

## Стек

- PHP 8.4, [Slim](https://www.slimframework.com/) 4
- PostgreSQL, PDO
- [Guzzle](https://docs.guzzlephp.org/) — HTTP-запросы
- [Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) — разбор HTML
- Bootstrap 5 (CDN)

## Запуск локально

```bash
composer install
export DATABASE_URL="pgsql://user:password@localhost:5432/dbname"
psql -d "$DATABASE_URL" -f database.sql
make start
```

Приложение будет доступно по адресу http://localhost:8000

### Hexlet tests and linter status

[![Actions Status](https://github.com/creiddom/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/creiddom/php-project-9/actions)

[![Lint](https://github.com/creiddom/php-project-9/actions/workflows/lint.yml/badge.svg)](https://github.com/creiddom/php-project-9/actions)

### Deploy

Live app: https://php-project-9-y7p7.onrender.com
