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

echo -e "\e[32m> Installing crontab ...\e[0m"
crontab ./app/Resources/server/cron/crontab
crontab -l
