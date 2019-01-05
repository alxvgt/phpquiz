#!/bin/bash

if [[ "$1" = "--help" ]]
then
echo ""
echo "Usage : bash update.sh"
echo ""
exit
fi

basedir=$(dirname "$0")

# Make sure we are in root dir
cd ${basedir}

echo -e "\e[32m> Installing dependencies ...\e[0m"
composer install -o -n 2>&1

echo -e "\e[32m> Setting cache and logs directories permissions ...\e[0m"
if [[ ! -d var/logs/cron/ ]]; then
    mkdir -p var/logs/cron/
fi
if [[ ! -d var/sessions ]]; then
    mkdir var/sessions
fi

HTTPDUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)
setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs var/sessions
setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs var/sessions
chown -R ${HTTPDUSER}:${HTTPDUSER} var/*

echo -e "\e[32m> Clearing and warming up caches ...\e[0m"
rm -rf var/cache/*
php bin/console cache:warmup --env=prod --no-debug
php bin/console cache:warmup --env=dev --no-debug

echo -e "\e[32m> Migrating database ...\e[0m"
php bin/console doctrine:migrations:migrate --env=prod -n

#echo -e "\e[32m> Dumping assets ...\e[0m"
#php bin/console assetic:dump --env=prod
