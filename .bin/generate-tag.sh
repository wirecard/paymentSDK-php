#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

set -e # Exit with nonzero exit code if anything fails

REPO=`git config remote.origin.url`
SSH_REPO=${REPO/https:\/\/github.com\//git@github.com:}

VERSION=`cat VERSION`
STATUS=`curl -s -o /dev/null -w "%{http_code}" -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/wirecard/paymentSDK-php/git/refs/tags/${VERSION}`

if [[ ${STATUS} == "200" ]] ; then
    echo "Tag is up to date with version."
    exit 0
elif [[ ${STATUS} != "404" ]] ; then
    echo "Got status ${STATUS} from GitHub. Exiting."
    exit 0
else
    echo "Version is updated, creating tag ${VERSION}"
fi

openssl aes-256-cbc -K ${encrypted_5b57bcef90c0_key} -iv ${encrypted_5b57bcef90c0_iv} -in deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval `ssh-agent -s`
ssh-add deploy_key

git config user.name "Travis CI"
git config user.email "wirecard@travis-ci.org"

git tag -a ${VERSION} -m "Pre-release version"

# Now that we're all set up, we can push.
git push ${SSH_REPO} master ${VERSION}