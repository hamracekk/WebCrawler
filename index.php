<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/PhPClasses/database.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$log = new Monolog\Logger('WebCrawlerLogger');
$log->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ .'/logs/app.log', Monolog\Logger::WARNING));

$log->error('Page is loaded');

$database = new Database();

$app = AppFactory::create();


$app->setBasePath("/WebCrawler");
$app->get('/hello', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->run();