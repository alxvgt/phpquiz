#!/bin/bash
CONTAINER_NAME=$1
CONTAINER_NAME=${CONTAINER_NAME:=php}
echo -e "\e[32m> Connecting to your container ...\e[0m"
gnome-terminal -x bash -c "sudo docker exec -ti $CONTAINER_NAME /bin/bash"