#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

export REPO_NAME='reports'
export REPO_ADDRESS="https://github.com/wirecard/$REPO_NAME.git"

#clone the repository where the screenshot should be uploaded
git clone ${REPO_ADDRESS}

#create folder with current date
export TODAY=$(date +%Y-%m-%d)
mkdir ${REPO_NAME}/${TODAY}

#copy report files
cp tests/_output/*.html ${REPO_NAME}/${TODAY}
cp tests/_output/*.fail.png ${REPO_NAME}/${TODAY}
cp tests/_output/*.xml ${REPO_NAME}/${TODAY}

cd ${REPO_NAME}

#push report files to the repository
git add ${TODAY}
git commit -m "Add failed test screenshots from ${TRAVIS_BUILD_WEB_URL}"
git push -q https://${GITHUB_TOKEN}@github.com/tatsta/${REPO_NAME}.git master

#save commit hash
export SCREENSHOT_COMMIT_HASH=$(git rev-parse --verify HEAD)

echo ${SCREENSHOT_COMMIT_HASH}
cd ..
#send slack notification
sh ./.bin/send-notify.sh
