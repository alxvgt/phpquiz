#!/bin/bash
echo -e "\e[32m> Stopping apache ...\e[0m"
sudo service apache2 stop #in order to free the 80 port

echo -e "\e[32m> Ensure entrypoint executable ...\e[0m"
chmod +x docker/php/entrypoint.sh

echo -e "\e[32m> Upping containers ...\e[0m"
sudo docker-compose up --remove-orphans -d

echo -e "\e[32m> Removing intermediates containers ...\e[0m"
sudo docker rmi $(sudo docker images -aq -f 'dangling=true')

echo -e "\e[32m> Waiting for services starting ...\e[0m"
sleep 2

echo -e "\e[32m> Docker containers status ...\e[0m"
sudo docker ps -a
