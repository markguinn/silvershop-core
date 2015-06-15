#!/usr/bin/env sh
#if [ "$COVERAGE" = "1" ] && [ "$TRAVIS_BRANCH" = "master" ]; then
if [ "$COVERAGE" = "1" ]; then
	vendor/bin/phpunit -c shop/phpunit.xml.dist --testsuite Split1 --coverage-clover=~/coverage.xml
elif [ "$COVERAGE" = "2" ]; then
	vendor/bin/phpunit -c shop/phpunit.xml.dist --testsuite Split2 --coverage-clover=coverage2.xml
elif [ "$COVERAGE" = "3" ]; then
	vendor/bin/phpunit -c shop/phpunit.xml.dist --testsuite Split3 --coverage-clover=shop/coverage3.xml
else
	vendor/bin/phpunit -c shop/phpunit.xml.dist
fi
