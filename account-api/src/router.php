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
$app->get('/status', 'App\Controller\DefaultController:getStatus');

$app->group('/api', function (RouteCollectorProxy $group) {
  $group->group('/user', function (RouteCollectorProxy $group) {
    $group->post('/login', Controller\User\Login::class);
    $group->get("/logout", Controller\User\Logout::class);
    $group->post('/create', Controller\User\Create::class);
  });

  $group->group('/task', function (RouteCollectorProxy $group) {
    $group->get('', Controller\Task\GetAll::class);
    $group->post('', Controller\Task\Create::class);
    $group->get('/{id}', Controller\Task\GetOne::class);
    $group->put('/{id}', Controller\Task\Update::class);
    $group->delete('/{id}', Controller\Task\Delete::class);
  })->add(new App\Middleware\Auth());
});

return $app;