#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install -y ruby-full
sudo gem install travis
travis login --org
touch .travis.yml && travis encrypt-file $1 --add
