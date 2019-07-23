#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

set -e # Exit with nonzero exit code if anything fails
#get version
export VERSION=`cat VERSION`

#start payment-sdk
php -S localhost:8080 > /dev/null &

# download and install ngrok
curl -s https://bin.equinox.io/c/4VmDzA7iaHb/ngrok-stable-linux-amd64.zip > ngrok.zip
unzip ngrok.zip
chmod +x $PWD/ngrok
# Download json parser for determining ngrok tunnel
curl -sO http://stedolan.github.io/jq/download/linux64/jq
chmod +x $PWD/jq

# Open ngrok tunnel
$PWD/ngrok authtoken $NGROK_TOKEN
TIMESTAMP=$(date +%s)
$PWD/ngrok http 8080 -subdomain=${TIMESTAMP}${GATEWAY}> /dev/null &

NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)
# allow ngrok to initialize
while [ ! ${NGROK_URL} ] || [ ${NGROK_URL} = 'null' ];  do
    echo "Waiting for ngrok to initialize"
    export NGROK_URL=$(curl -s localhost:4040/api/tunnels/command_line | jq --raw-output .public_url)
    sleep 1
done

#run tests
vendor/bin/codecept run acceptance -g "${GATEWAY}" --html --xml
