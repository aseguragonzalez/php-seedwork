.PHONY: all format format-check lint static-analyse test install clean update-autoload create-package

all: format-check lint static-analyse test

clean:
	@rm -rf vendor
	@rm -rf coverage
	@rm -rf .phpunit.cache
	@rm -rf .php-cs-fixer.cache
	@rm -rf dist

format:
	@./vendor/bin/php-cs-fixer fix . --rules=@PSR12

format-check:
	@./vendor/bin/php-cs-fixer fix . --rules=@PSR12 --dry-run --diff

install:
	@pre-commit install
	@composer install
	@export PATH=$PATH:./vendor/bin

lint:
	@./vendor/bin/phpcs --standard=PSR12 ./src ./tests

static-analyse:
	@rm -rf /tmp/phpstan/cache
	@./vendor/bin/phpstan analyse ./src ./tests --level=max --memory-limit=1G

test:
	@./vendor/bin/phpunit -c phpunit.xml --coverage-html coverage/

update-autoload:
	@composer dump-autoload

create-package:
	@mkdir -p dist
	@composer archive --format=zip --dir=dist
