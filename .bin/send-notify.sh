#!/bin/bash
PREVIEW_LINK='http://htmlpreview.github.io/?https://raw.githubusercontent.com/wirecard/reports'
REPORT_FILE='report.html'
#choose slack channel depending on the gateway
if [[ ${GATEWAY} = "NOVA" ]]; then
  CHANNEL='shs-ui-nova'
elif [[ ${GATEWAY} = "API-WDCEE-TEST" ]]; then
  CHANNEL='shs-ui-api-wdcee-test'
elif [[  ${GATEWAY} = "API-TEST" ]]; then
   CHANNEL='shs-ui-api-test'
elif [[  ${GATEWAY} = "TEST-SG" ]]; then
   CHANNEL='shs-ui-test-sg'
elif [[  ${GATEWAY} = "SECURE-TEST-SG" ]]; then
   CHANNEL='shs-ui-secure-test-sg'
fi

#send information about the build
curl -X POST -H 'Content-type: application/json' \
    --data "{'text': 'Build Failed. Build URL : ${TRAVIS_JOB_WEB_URL}\n
    Build Number: ${TRAVIS_BUILD_NUMBER}\n
    Branch: ${TRAVIS_BRANCH}', 'channel': '${CHANNEL}'}" ${SLACK_ROOMS}

FAILED_TESTS=$(ls -1q tests/_output/*.fail.png | wc -l)

if ((${FAILED_TESTS} > 3 )); then
  # do not send more than 3 screenshots to the chat room
    curl -X POST -H 'Content-type: application/json' --data "{
        'attachments': [
            {
                'fallback': 'Failed test data',
                'text': 'There are ${FAILED_TESTS} failed tests.
                 All screenshots can be found  ${REPO_LINK}/tree/${SCREENSHOT_COMMIT_HASH}/${PROJECT_FOLDER}/${GATEWAY}/${TODAY} .
                 Please see ${PREVIEW_LINK}/blob/${SCREENSHOT_COMMIT_HASH}/${PROJECT_FOLDER}/${GATEWAY}/${TODAY}/${REPORT_FILE} for detailed info.',
                'color': '#764FA5'
            }
        ], 'channel': '${CHANNEL}'
    }"  ${SLACK_ROOMS};
else
  #send links to all screenshots obtained
    for f in tests/_output/*.fail.png; do
        FILENAME=$(basename -- "${f}")
        TESTNAME="${FILENAME%%.fail.*}"

        #send screenshot links
        curl -X POST -H 'Content-type: application/json' --data "{
            'attachments': [
                {
                    'fallback': 'Failed test screenshot',
                    'text': 'See screenshot of  failed ${TESTNAME} test here: \
                    ${REPO_LINK}/tree/${SCREENSHOT_COMMIT_HASH}/${PROJECT_FOLDER}/${GATEWAY}/${TODAY}/${FILENAME} \
                    More info in report ${PREVIEW_LINK}/blob/${SCREENSHOT_COMMIT_HASH}/${PROJECT_FOLDER}/${GATEWAY}/${TODAY}/${REPORT_FILE}',
                    'color': '#764FA5'
                }
            ], 'channel': '${CHANNEL}'
        }"  ${SLACK_ROOMS};
    done
fi






