#!/usr/bin/env bash

if [[ "$1" = "--help" ]]
then
echo ""
echo "Usage : bash install.sh"
echo ""
exit
fi

echo -e "\e[32m> Ensure git configuration ...\e[0m"
chmod -R 0777 app/Resources/git
git init
git config core.fileMode false
git config core.autocrlf input
git config color.ui auto
cp -f app/Resources/git/hooks/pre-commit .git/hooks/pre-commit

echo -e "\e[32m> Create cron logs directory ...\e[0m"
if [[ ! -d var/logs/cron/ ]]; then
    mkdir -p var/logs/cron/
fi

echo -e "\e[32m> Installing dependencies ...\e[0m"
composer install -o -n 2>&1

echo -e "\e[32m> Installing crontab ...\e[0m"
crontab ./app/Resources/server/cron/crontab
crontab -l

echo -e "\e[32m> Creating database ...\e[0m"
env
apt-get update && apt-get install -y netcat && nc -zv database 3306
php bin/console doctrine:database:create --if-not-exists
