#!/usr/bin/env bash

if [[ "$1" = "--help" ]]
then
echo ""
echo "Usage :"
echo "bash reset-data.sh"
echo ""
exit
fi

path=$PWD

echo ""

if [[ "$1" != "--force" ]]
then
    asking="Attention ! Ce script va supprimer l'ensemble des données. Cette action est irréversible. Êtes-vous sûr de vouloir continuer [y] ? (y/n)
    "
    read -p "$(echo -e "\033[0;31m $asking \033[0m")" response
    response=${response:-y}
    if [[ ${response} != "yes" && ${response} != "y" ]]
    then
        exit 1
    fi
fi

echo -e "\e[32m> Installing dependencies ...\e[0m"
composer install -o -n 2>&1

echo -e "\e[32m> Dropping database ...\e[0m"
php bin/console doctrine:database:drop --if-exists --force

echo -e "\e[32m> Clearing caches...\e[0m"
rm -rf var/cache/*
rm -rf var/sessions/*
