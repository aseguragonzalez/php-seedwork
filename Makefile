.PHONY: all install check cs cs-fix stan test test-no-coverage test-examples check-layer-boundaries clean update-autoload create-package docs-serve

install:
	composer install

cs:
	composer cs

cs-fix:
	composer cs:fix

stan:
	composer stan

test:
	composer test

test-no-coverage:
	composer test:no-coverage

test-examples:
	composer test:examples

check-layer-boundaries:
	@! find tests/ -name "*.php" -exec grep -l "use Examples\\" {} + 2>/dev/null | grep -q . || \
		(printf '\nERROR: tests/ must not import from Examples\\\n'; exit 1)

check: check-layer-boundaries
	composer check

all: install cs-fix check

clean:
	@rm -rf vendor coverage .phpunit.cache .php-cs-fixer.cache dist

update-autoload:
	composer dump-autoload

create-package:
	@mkdir -p dist
	@composer archive --format=zip --dir=dist

docs-serve:
	@command -v mkdocs >/dev/null 2>&1 || \
		(printf '%s\n' 'ERROR: mkdocs was not found in PATH. Activate the project virtualenv or use the documented devcontainer setup before running `make docs-serve`.'; exit 1)
	@mkdocs serve --dev-addr=0.0.0.0:8001
