<?php

require_once __DIR__.'/../vendor/autoload.php';

// get app
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../src/controllers.php';

// run it
$app->run();
