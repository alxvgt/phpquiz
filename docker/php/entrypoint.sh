#!/usr/bin/env bash
cron -f &
docker-php-entrypoint php-fpm
