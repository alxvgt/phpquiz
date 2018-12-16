#!/bin/bash
echo -e "\e[32mStopping containers ...\e[0m"
sudo docker stop $(sudo docker ps -a -q);
echo -e "\e[32mRemoving containers ...\e[0m"
sudo docker rm $(sudo docker ps -a -q);
echo -e "\e[32mRemoving images ...\e[0m"
sudo docker rmi $(sudo docker images -q);
