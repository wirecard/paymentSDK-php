#!/bin/bash

#choose slack channel depending on the gateway
if [[ "$GATEWAY" = "NOVA" ]]; then
  CHANNEL='shs-ui-nova'
elif [[ "$GATEWAY" = "API-WDCEE-TEST" ]]; then
  CHANNEL='shs-ui-api-wdcee-test'
elif [[ "$GATEWAY" = "API-TEST" ]]; then
   CHANNEL='shs-ui-api-test'
fi

#send information about the build
curl -X POST -H 'Content-type: application/json' \
    --data "{'text': 'Build Failed. Build URL : $TRAVIS_JOB_WEB_URL\n
    Build Number: $TRAVIS_BUILD_NUMBER\n
    Branch: $TRAVIS_BRANCH', 'channel': '$CHANNEL'}" $SLACK_ROOMS

#send links to all screenshots obtained
for f in tests/_output/*.fail.png; do
    FILENAME=$(basename -- "$f")
    echo "FILENAME=$FILENAME"
    TESTNAME="${FILENAME%.*}"
    echo "TESTNAME=$TESTNAME"


    #send links to screenshots
    curl -X POST -H 'Content-type: application/json' --data "{
        'attachments': [
            {
                'fallback': 'Failed test screenshot',
                'text': 'Test {$f} Screenshot ${REPO_ADDRESS}/tree/${SCREENSHOT_COMMIT_HASH}/${TODAY}/${FILENAME}',
                'color': '#764FA5'
            }
        ]
    }"  $SLACK_ROOMS;
done






