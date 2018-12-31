#!/usr/bin/env bash
bash install.sh
bash update.sh
cron -f &
docker-php-entrypoint php-fpm
