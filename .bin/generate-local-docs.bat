:: Shop System SDK:
:: - Terms of Use can be found under:
:: https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
:: - License can be found under:
:: https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE

:: Run this with PHP 5.6 not PHP7
rm -rf doc
call  php -f apigen.phar generate -s src -d doc --template-theme="bootstrap"
call  groc -o doc/ examples/*/*.php
call  groc -o doc/ examples/*/*/*.php
cp --parents examples/index.html doc/
cp --parents examples/payments/*/menu.html doc/
cp --parents examples/inc/features/*-menu.html doc/
cp --parents examples/configuration/*-menu.html doc/
cp docs/* doc/
cp examples/*.html doc/examples/
cp --parents -r examples/assets/* doc/
