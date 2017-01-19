<?php

// # Framework example
// This example displays the usage of the Framework class.

use Wirecard\PaymentSdk\Framework;

// ## Hello world using the framework

$framework = new Framework();

// Get the "Hello world"-string
$framework->hello( "world" );
