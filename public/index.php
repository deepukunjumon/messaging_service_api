<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$container = require __DIR__ . '/../src/container.php';

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();

// Load routes
(require __DIR__ . '/../src/routes.php')($app);

$app->run();