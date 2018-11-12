#!/bin/bash

if [[ "$GATEWAY" = "NOVA" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR\n'}" $NOVA-SLACK

elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR'}" $API-WDCEE-TEST-SLACK

else
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_JOB_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR'}" $API-TEST-SLACK
fi