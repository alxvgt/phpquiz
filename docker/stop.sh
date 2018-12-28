#!/bin/bash
echo -e "\e[32m> Downing containers ...\e[0m"
sudo docker-compose down --remove-orphans
