#!/usr/bin/env bash

source .deploy.env
mkdir -p ~/.ssh
ssh-keyscan -t rsa github.com >> ~/.ssh/known_hosts
test -d ${SERVER_APP_PATH} && (cd ${SERVER_APP_PATH} && git checkout master && git reset --hard origin/master && git pull) || git clone git@github.com:alxvgt/phpquiz.git ${SERVER_APP_PATH}
cat .deploy.env .env > ${SERVER_APP_PATH}/.env
cd ${SERVER_APP_PATH}
bash docker/start.sh
docker exec php /bin/bash -c 'bash install.sh'
docker exec php /bin/bash -c 'sleep 2; bash reset-data.sh --force'
docker exec php /bin/bash -c 'bash update.sh'
docker exec php /bin/bash -c 'bash test.sh'