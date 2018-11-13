#!/bin/bash

if [[ "$GATEWAY" = "NOVA" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR', 'channel': 'shs-ui-nova'}" $SLACK-ROOMS

elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR', 'channel': 'shs-ui-api-wdcee-test'}" $SLACK-ROOMS

else
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR', 'channel': 'shs-ui-api-test'}" $SLACK-ROOMS
fi
