<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Cookies;

abstract class BaseController
{
  protected function jsonResponse(Response $response, string $status, $message, int $code): Response 
  {
    $result = [
        'code'   => $code,
        'status' => $status,
        'data'   => $message
    ];

    $response->getBody()->write(json_encode($result));
    return $response
      ->withHeader('content-type', 'application/json')
      ->withStatus($code);
  }

  protected function getUserId(Request $request): string 
  {
    return $request->getAttribute('jwt')->sub;
  }
}