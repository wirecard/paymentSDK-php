#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

set -e # Exit with nonzero exit code if anything fails
TARGET_DIRECTORY="target"
TARGET_VERSION=$1
COPY_PATTERN="!(.*|vendor|clover.xml|sonar-project.properties|deploy_key.enc|target|apigen.phar|node_modules|gh-pages-upload-tmp)"

rm -rf $TARGET_DIRECTORY
echo "copying files to target directory ${TARGET_DIRECTORY}"
mkdir $TARGET_DIRECTORY
shopt -s extglob
cp -R ${COPY_PATTERN} ${TARGET_DIRECTORY}/
shopt -u extglob

cd $TARGET_DIRECTORY

composer install --no-dev
zip -r wirecard-paymentSDK-php-${TARGET_VERSION}.zip .