#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

REPO=`git config remote.origin.url`
SSH_REPO=${REPO/https:\/\/github.com\//git@github.com:}

TAG=`git describe --tags --abbrev=0`
VERSION=`cat VERSION`

echo "Tag and version information:"
echo "Latest tag: $TAG"
echo "Version set: $VERSION"

if [[ ${TAG} == ${VERSION} ]] ; then
    echo "Tag and version are different!"
fi

git config user.name "Travis CI"
git config user.email "wirecard@travis-ci.org"

# git commit -m "Create release tag for version $VERSION"

openssl aes-256-cbc -K ${encrypted_5b57bcef90c0_key} -iv ${encrypted_5b57bcef90c0_iv} -in deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval `ssh-agent -s`
ssh-add deploy_key

# Now that we're all set up, we can push.
# git push ${SSH_REPO} master
