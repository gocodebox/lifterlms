#!/usr/bin/env bash
# usage: travis.sh before|after

if [ $1 == 'before' ]; then

	composer self-update
	composer install --no-interaction

elif [ $1 == 'during' ]; then

	# ensure that we exit as soon as we encounter an error
	set -e

	# lint
	find -L . -path ./vendor -prune -o -name '*.php' -print0 | xargs -0 -n 1 -P 4 php -l;

	# phpcs
	composer run-script phpcs

	# phpunit
	composer run-script phpunit --coverage-clover build/logs/clover.xml

fi
