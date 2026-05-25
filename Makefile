PORT ?= 8000

.PHONY: help install start test lint lint-phpcs lint-phpstan db

.DEFAULT_GOAL := help

help:
	@echo "Доступные команды:"
	@echo "  make install       — composer install"
	@echo "  make start         — сервер: http://localhost:$(PORT)"
	@echo "  make test          — phpunit + coverage.xml (для Sonar)"
	@echo "  make lint          — phpcs + phpstan (перед пушем)"
	@echo "  make lint-phpcs    — только стиль кода (phpcs)"
	@echo "  make lint-phpstan  — только статический анализ (phpstan)"
	@echo "  make db            — применить database.sql (нужен DATABASE_URL)"

install:
	composer install

test: vendor/bin/phpunit
	@php vendor/bin/phpunit --coverage-clover=coverage.xml
	@echo "coverage.xml создан"

vendor/bin/phpunit vendor/bin/phpcs vendor/bin/phpstan: composer.json composer.lock
	composer install

LOCAL_DATABASE_URL ?= postgresql://localhost:5432/page_analyzer

start:
	DATABASE_URL="$${DATABASE_URL:-$(LOCAL_DATABASE_URL)}" \
	php -S 0.0.0.0:$(PORT) -t public public/index.php

lint: lint-phpcs lint-phpstan

lint-phpcs: vendor/bin/phpcs
	@echo "Проверка стиля кода (phpcs)..."
	@php vendor/bin/phpcs
	@echo "phpcs: OK"

lint-phpstan: vendor/bin/phpstan
	@echo "Статический анализ (phpstan)..."
	@php vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=256M
	@echo "phpstan: OK"

db:
	DATABASE_URL="$${DATABASE_URL:-$(LOCAL_DATABASE_URL)}" \
	psql -d "$$DATABASE_URL" -f database.sql
