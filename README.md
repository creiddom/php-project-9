# Hexlet tests and linter status

[![Actions Status](https://github.com/creiddom/php-project-9/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/creiddom/php-project-9/actions) [![Lint](https://github.com/creiddom/php-project-9/actions/workflows/lint.yml/badge.svg)](https://github.com/creiddom/php-project-9/actions) [![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=creiddom_php-project-9&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=creiddom_php-project-9)

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
createdb page_analyzer   # один раз, если базы ещё нет

export DATABASE_URL="postgresql://localhost:5432/page_analyzer"
# с логином: postgresql://user:password@localhost:5432/page_analyzer

make db
make start
```

`make start` и `make db` подставят тот же URL, если переменная не экспортирована — но **лучше задать `DATABASE_URL` явно** (в терминале или в Render).

На Render: **Environment → DATABASE_URL** = External Database URL из панели PostgreSQL.

Приложение будет доступно по адресу http://localhost:8000

### Deploy

Live app: https://php-project-9-y7p7.onrender.com
