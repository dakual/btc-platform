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

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($request, $handler) {
  $response = $handler->handle($request);
  return $response
      ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
      ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
      ->withHeader('Access-Control-Allow-Credentials', 'true')
      ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', 'App\Controller\DefaultController:getMain');

$app->group('/api', function (RouteCollectorProxy $group) {
  $group->group('/wallet', function (RouteCollectorProxy $group) {
    $group->get('', Controller\GetWallet::class);
    $group->post('', Controller\CreateWallet::class);
  })->add(new App\Middleware\Auth());

  $group->group('/withdraw', function (RouteCollectorProxy $group) {
    $group->get('', Controller\GetAllWithdraws::class);
    $group->post('', Controller\Withdraw::class);
  })->add(new App\Middleware\Auth());

  $group->group('/tx', function (RouteCollectorProxy $group) {
    $group->get('', Controller\GetTransaction::class);
  })->add(new App\Middleware\Auth());
});

return $app;