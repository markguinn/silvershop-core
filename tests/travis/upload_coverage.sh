#!/usr/bin/env bash
echo "coverage = $COVERAGE"
if [ -n "$COVERAGE" ]; then
	cd shop
	../vendor/bin/ocular code-coverage:upload -v --format=php-clover ~/builds/ss/shop/coverage.xml
fi
