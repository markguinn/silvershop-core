#!/usr/bin/env sh
#if [ "$COVERAGE" > 0 ]; then
#	echo "Running coverage..."
#	mkdir ~/builds/ss/shop/build
#	mkdir ~/builds/ss/shop/build/logs
#	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist --coverage-clover ~/builds/ss/shop/build/logs/clover.xml
if [ "$COVERAGE" = 1 ]; then
	mkdir ~/builds/ss/shop/build
	mkdir ~/builds/ss/shop/build/logs
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist --testsuite Split1 --coverage-clover ~/builds/ss/shop/build/logs/clover.xml
#elif [ "$COVERAGE" = "2" ] && [ "$TRAVIS_BRANCH" = "master" ]; then
elif [ "$COVERAGE" = "2" ]; then
	mkdir ~/builds/ss/shop/build
	mkdir ~/builds/ss/shop/build/logs
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist --testsuite Split2 --coverage-clover ~/builds/ss/shop/build/logs/clover.xml
elif [ "$COVERAGE" = "3" ]; then
	mkdir ~/builds/ss/shop/build
	mkdir ~/builds/ss/shop/build/logs
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist --testsuite Split3 --coverage-clover ~/builds/ss/shop/build/logs/clover.xml
else
	vendor/bin/phpunit -c ~/builds/ss/shop/phpunit.xml.dist
fi
