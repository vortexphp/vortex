.PHONY: help setup dev build test serve

help:
	@echo "Targets:"
	@echo "  make setup   composer + npm install, copy .env if missing"
	@echo "  make dev     Tailwind CSS watch (npm run dev)"
	@echo "  make build   Tailwind CSS + Live JS (composer run build)"
	@echo "  make test    PHPUnit"
	@echo "  make serve   php vortex serve"

setup:
	composer install
	npm install
	test -f .env || cp .env.example .env

dev:
	npm run dev

build:
	composer run build

test:
	composer run test

serve:
	php vortex serve
