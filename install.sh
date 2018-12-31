#!/usr/bin/env bash

echo -e "\e[32m> Installing crontab ...\e[0m"
crontab ./app/Resources/server/cron/crontab
crontab -l