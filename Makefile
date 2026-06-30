DC = docker compose run --rm php

.PHONY: install test cs cs-fix shell

install:
	$(DC) composer install --no-interaction

test:
	$(DC) vendor/bin/phpunit

cs:
	$(DC) vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix:
	$(DC) vendor/bin/php-cs-fixer fix

shell:
	$(DC) bash
