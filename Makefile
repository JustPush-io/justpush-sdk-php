cs:      ## Run code style fixer
	PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix -vv

test:
	./vendor/bin/phpunit tests