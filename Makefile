.PHONY: all format format-check lint static-analyse test test-examples check-layer-boundaries install clean update-autoload create-package

all: format-check lint static-analyse check-layer-boundaries test

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

check-layer-boundaries:
	@! find tests/ -name "*.php" -exec grep -l "use Examples\\\\" {} + 2>/dev/null | grep -q . || (printf '\nERROR: tests/ must not import from Examples\\\n'; exit 1)

test:
	@./vendor/bin/phpunit -c phpunit.xml --testsuite default --coverage-html coverage/

test-examples:
	@./vendor/bin/phpunit -c phpunit.xml --testsuite examples

update-autoload:
	@composer dump-autoload

create-package:
	@mkdir -p dist
	@composer archive --format=zip --dir=dist
