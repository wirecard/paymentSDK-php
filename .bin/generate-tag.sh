#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

REPO=`git config remote.origin.url`
SSH_REPO=${REPO/https:\/\/github.com\//git@github.com:}

VERSION=`cat VERSION`
STATUS=`curl -s -o /dev/null -w "%{http_code}" https://api.github.com/repos/wirecard/paymentSDK-php/git/refs/tags/${VERSION}`

if [[ ${STATUS} == "200" ]] ; then
    echo "Tag is up to date with version."
    exit 0
fi

echo "Version is updated, creating tag ${VERSION}"

openssl aes-256-cbc -K ${encrypted_5b57bcef90c0_key} -iv ${encrypted_5b57bcef90c0_iv} -in deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval `ssh-agent -s`
ssh-add deploy_key

git config user.name "Travis CI"
git config user.email "wirecard@travis-ci.org"

git tag -a ${VERSION}

# Now that we're all set up, we can push.
git push ${SSH_REPO} master ${VERSION}
