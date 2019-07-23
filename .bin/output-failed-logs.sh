#!/bin/bash
# Shop System SDK:
# - Terms of Use can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
# - License can be found under:
# https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

set -e # Exit with nonzero exit code if anything fails

for f in tests/_output/*.fail.html; do
echo "----------------------<$f>----------------------";
cat "$f"; done