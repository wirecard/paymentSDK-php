#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

export REPO_NAME='reports'
export REPO_LINK="https://github.com/wirecard/${REPO_NAME}"
export REPO_ADDRESS="${REPO_LINK}.git"

#clone the repository where the screenshot should be uploaded
git clone ${REPO_ADDRESS}

#create folder with current date
export TODAY=$(date +%Y-%m-%d)

export PROJECT_FOLDER="paymentSDK-php"
GATEWAY_FOLDER=${REPO_NAME}/${PROJECT_FOLDER}/${GATEWAY}
DATE_FOLDER=${GATEWAY_FOLDER}/${TODAY}

if [ ! -d "${GATEWAY_FOLDER}" ]; then
mkdir ${GATEWAY_FOLDER}
fi

if [ ! -d "${DATE_FOLDER}" ]; then
mkdir ${DATE_FOLDER}
fi

#copy report files
cp tests/_output/*.html ${DATE_FOLDER}
cp tests/_output/*.fail.png ${DATE_FOLDER}

cd ${REPO_NAME}
#push report files to the repository
git add ${PROJECT_FOLDER}/${GATEWAY}/${TODAY}/*
git commit -m "Add failed test screenshots from ${TRAVIS_BUILD_WEB_URL}"
git push -q https://${GITHUB_TOKEN}@github.com/wirecard/${REPO_NAME}.git master

#save commit hash
export SCREENSHOT_COMMIT_HASH=$(git rev-parse --verify HEAD)

#send slack notification
cd ..
bash .bin/send-notify.sh
