PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:8000 -t public

lint:
	./vendor/bin/phpcs src