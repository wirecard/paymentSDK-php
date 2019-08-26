#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

set -e # Exit with nonzero exit code if anything fails

SOURCE_BRANCH="master"
TARGET_BRANCH="gh-pages"
UPLOAD_DIRECTORY="gh-pages-upload-tmp"

# Save some useful information
REPO=`git config remote.origin.url`
SSH_REPO=${REPO/https:\/\/github.com\//git@github.com:}
SHA=`git rev-parse --verify HEAD`

# Clone the existing gh-pages for this repo into ${UPLOAD_DIRECTORY}/
# Create a new empty branch if gh-pages doesn't exist yet (should only happen on first deploy)

echo "Clone GitHub repository:"
git clone -q ${REPO} ${UPLOAD_DIRECTORY}
cd ${UPLOAD_DIRECTORY}
git checkout ${TARGET_BRANCH} || git checkout --orphan ${TARGET_BRANCH}
cd ..

# Clean UPLOAD_DIRECTORY existing contents
echo "Cleanup ..."
rm -rf ${UPLOAD_DIRECTORY}/**/* || exit 0

## Compile the docs
echo "Create reference with ApiGen:"

# ApiGen: Download
wget -q http://apigen.org/apigen.phar

# ApiGen: generate the reference
vendor/bin/apigen generate -s src -d ${UPLOAD_DIRECTORY}/docs --template-theme="bootstrap"
# Add custom styles
cat docs/apigen.css >> ${UPLOAD_DIRECTORY}/docs/resources/style.css


# groc: install
echo "Create example documentation with groc:"
npm install -q -g groc

# groc: generate the examples
groc -o ${UPLOAD_DIRECTORY}/ examples/*/*.php
groc -o ${UPLOAD_DIRECTORY}/ examples/*/*/*.php

# Copy the main pages to UPLOAD_DIRECTORY
cp docs/* ${UPLOAD_DIRECTORY}/
cp examples/*.html ${UPLOAD_DIRECTORY}/examples/
# Copy menu
cp --parents examples/payments/*/menu.html ${UPLOAD_DIRECTORY}/
cp --parents examples/inc/features/*-menu.html ${UPLOAD_DIRECTORY}/
cp --parents examples/configuration/*-menu.html ${UPLOAD_DIRECTORY}/
#Copy assets
cp --parents -r examples/assets/* ${UPLOAD_DIRECTORY}/
# Prepare the cloned repository for push
echo "Upload documentation to GitHub Pages:"
cd ${UPLOAD_DIRECTORY}
git config user.name "Travis CI"
git config user.email "wirecard@travis-ci.org"

# If there are no changes to the compiled ${UPLOAD_DIRECTORY} (e.g. this is a README update) then just bail.
if [[ -z "$(git status --porcelain)" ]]; then
    echo "No changes to the documentation on this build. Exiting."
    exit 0
fi

# Commit the "changes", i.e. the new version.
# The delta will show diffs between new and old versions.
git add --all .
git commit -m "Deploy documentation to GitHub Pages: ${SHA}"

# Get the deploy key by using Travis's stored variables to decrypt deploy_key.enc
openssl aes-256-cbc -K ${encrypted_5b57bcef90c0_key} -iv ${encrypted_5b57bcef90c0_iv} -in ../deploy_key.enc -out deploy_key -d
chmod 600 deploy_key
eval `ssh-agent -s`
ssh-add deploy_key

# Now that we're all set up, we can push.
git push ${SSH_REPO} ${TARGET_BRANCH}
