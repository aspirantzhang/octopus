phpcbf -p --colors --standard=PSR12 --extensions=php,snap ./app ./tests && phpcs --colors --standard=PSR12 -n -p ./app ./tests && .\vendor\bin\phpstan analyse && php think misc:deleteTable && php think migrate:run && .\vendor\bin\phpunit --configuration ./phpunit.xml.dist --coverage-clover runtime/.phpunit.cache/coverage.xml && php think misc:deleteTable && php think migrate:run
