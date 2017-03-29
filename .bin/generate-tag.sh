#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

cd $HOME/wirecard/paymentSDK-php/

TAG=`git describe --tags --abbrev=0`
VERSION=`cat VERSION`

echo "Tag and version information:"
echo "Latest tag: $TAG"
echo "Version set: $VERSION"

if [[ $TAG == $VERSION ]] ; then
    echo "Tag and version are different!"
fi
