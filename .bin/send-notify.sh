#!/bin/bash

if [[ "$GATEWAY" = "NOVA" ]]; then
  CHANNEL='shs-ui-nova'
elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
  CHANNEL='shs-ui-api-wdcee-test'
elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
   CHANNEL='shs-ui-api-test'
fi

curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build Failed. Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH', 'channel': '$CHANNEL'}" $SLACK_ROOMS
