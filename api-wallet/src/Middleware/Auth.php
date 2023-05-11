<?php
namespace App\Middleware;

use App\Utils\Settings;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
  public $settings;

  public function __construct()
  {
    $this->settings = Settings::getSettings();
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    $jwtHeader = $request->getHeaderLine('Authorization');
    if (! $jwtHeader) {
      throw new \Exception('JWT Token required.', 400);
    }

    $jwt = explode('Bearer ', $jwtHeader);
    if (! isset($jwt[1])) {
      throw new \Exception('JWT Token invalid.', 400);
    }

    $decoded  = $this->checkToken($jwt[1]);
    $request  = $request->withAttribute("jwt", $decoded);
    $response = $handler->handle($request);

    return $response;
  }

  private function checkToken(string $token): object
  {
    try {
      return JWT::decode($token, new Key($this->settings->jwt->public_key, 'RS256'));
    } catch (\Exception $ex) {
      throw new \Exception('Forbidden: you are not authorized.', 403);
    }
  }
}
