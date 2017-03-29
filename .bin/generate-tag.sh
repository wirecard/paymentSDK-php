#!/bin/bash
set -e # Exit with nonzero exit code if anything fails

cd $HOME/wirecard/paymentSDK-php/

TAG=`git describe --tags --abbrev=0`
VERSION=`cat VERSION`

