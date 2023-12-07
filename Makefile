PORT ?= 8000
start:
	PHP_CLI_SERVER_WORKERS=5 php -c /etc/php/8.1/apache2/php.ini -S 0.0.0.0:$(PORT) -t public

install:
	composer install

lint:
	composer exec phpcs -- --standard=PSR2 src/ public/