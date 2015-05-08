<?php

include __DIR__ . '/vendor/autoload.php';

// instantiate
$loader = new \Aura\Autoload\Loader;

// append to the SPL autoloader stack; use register(true) to prepend instead
$loader->register();

$loader->addPrefix('Augwa\PaymentGateway', __DIR__ . '/src');