#!/usr/bin/env bash

source .deploy.env
test -d ${SERVER_APP_PATH} && (cd ${SERVER_APP_PATH} && git checkout master && git reset --hard origin/master && git pull) || git clone git@github.com:alxvgt/phpquiz.git ${SERVER_APP_PATH}
cp -f .env ${SERVER_APP_PATH}/.env
cp -f .deploy.env ${SERVER_APP_PATH}/.deploy.env
cd ${SERVER_APP_PATH}
ls -halt
cat .env
cat .deploy.env