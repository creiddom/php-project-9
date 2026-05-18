PORT ?= 8000

.PHONY: help install start lint db

.DEFAULT_GOAL := help

help:
	@echo "Доступные команды:"
	@echo "  make install  — установить зависимости (composer install)"
	@echo "  make start    — запустить сервер: http://localhost:$(PORT)"
	@echo "  make lint     — проверить стиль кода (как в GitHub Actions)"
	@echo "  make db       — создать таблицы в PostgreSQL (нужен DATABASE_URL)"
	@echo ""
	@echo "Порядок для первого запуска: install → db → start"
	@echo "Перед make lint нужен make install (устанавливает phpcs)"

install:
	composer install

vendor/bin/phpcs: composer.json composer.lock
	composer install

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public public/index.php

lint: vendor/bin/phpcs
	@echo "Проверка стиля кода (phpcs)..."
	@php vendor/bin/phpcs
	@echo "Готово: замечаний нет."

db:
	psql -d "$(DATABASE_URL)" -f database.sql
