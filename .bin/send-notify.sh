#!/bin/bash

if [[ "$GATEWAY" = "NOVA" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_BUILD_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR\n'}" https://hooks.slack.com/services/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

elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_BUILD_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR'}" https://hooks.slack.com/services/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

else
  curl -X POST -H 'Content-type: application/json' --data "{'text': 'Build URL : $TRAVIS_BUILD_WEB_URL\nBuild Number : $TRAVIS_BUILD_NUMBER\nBranch : $TRAVIS_BRANCH\nRepository: $TRAVIS_BUILD_DIR'}" https://hooks.slack.com/services/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
fi