#!/usr/bin/env bash

source .deploy.env
mkdir -p ~/.ssh
ssh-keyscan -t rsa github.com >> ~/.ssh/known_hosts
test -d ${SERVER_APP_PATH} && (cd ${SERVER_APP_PATH} && git checkout master && git reset --hard origin/master && git pull) || git clone git@github.com:alxvgt/phpquiz.git ${SERVER_APP_PATH}
cp -f .env ${SERVER_APP_PATH}/.env
cp -f .deploy.env ${SERVER_APP_PATH}/.deploy.env
cd ${SERVER_APP_PATH}
bash docker/start.sh
docker exec -ti php /bin/bash -c 'bash install.sh'
docker exec -ti php /bin/bash -c 'sleep 2; bash reset-data.sh --force'
docker exec -ti php /bin/bash -c 'bash update.sh'
docker exec -ti php /bin/bash -c 'bash test.sh'