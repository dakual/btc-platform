<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use App\Controller;
use App\Handler\ErrorHandler;


require __DIR__ . '/../vendor/autoload.php';


$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler    = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->options("[{routes.*}]", function(Request $req, Response $res, array $args) :Response { return $res; });
$app->get('/', 'App\Controller\DefaultController:getMain');

$app->group('/api', function (RouteCollectorProxy $group) {
  $group->group('/wallet', function (RouteCollectorProxy $group) {
    $group->get('', Controller\GetWallet::class);
    $group->post('', Controller\CreateWallet::class);
  })->add(new App\Middleware\Auth());

  $group->group('/tx', function (RouteCollectorProxy $group) {
    $group->get('', Controller\GetTransaction::class);
    $group->post('', Controller\CreateTransaction::class);
    $group->post('/send', Controller\SendTransaction::class);
  })->add(new App\Middleware\Auth());
});

return $app;