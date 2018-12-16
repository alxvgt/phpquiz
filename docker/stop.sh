#!/bin/bash
echo -e "\e[32m> Downing containers ...\e[0m"
docker-compose down --remove-orphans
