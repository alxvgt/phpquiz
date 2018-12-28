#!/bin/bash

PHP_CS_FIXER_IGNORE_ENV=1 php vendor/bin/php-cs-fixer fix src --rules=@PSR1,@PSR2,@Symfony,@Symfony:risky --allow-risky=yes