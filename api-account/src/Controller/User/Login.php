<?php
namespace App\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Entity\UserEntity;
use Firebase\JWT\JWT;


class Login extends BaseController
{
  private $jwtPrivateKey;
  private $jwtLifeTime;
  private UserRepository $repository;

  public function __construct()
  {
    $this->jwtPrivateKey = getenv('JWT_PRIVATE_KEY');
    $this->jwtLifeTime   = getenv('JWT_LIFETIME');
    $this->jwtIssuer     = getenv('JWT_ISSUER');
    $this->repository    = new UserRepository();
  }

  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();
    if (empty($data) || !isset($data['username']) || !isset($data['password'])) {
      throw new \App\Exception\Auth(
        'Login failed: Username and Password required!', 400
      );
    }
    $username = $data["username"];
    $password = $data["password"];

    $user = $this->repository->loginUser($username, $password);
    if (! password_verify($password, $user->password)) {
      throw new \App\Exception\Auth(
        'Login failed: Username or password incorrect!', 400
      );
    }
    
    $token = [
      "iss"  => $this->jwtIssuer,
      "aud"  => "http://example.com",
      "sub"  => $user->id,
      "iat"  => time(),
      "exp"  => time() + ((int) $this->jwtLifeTime)
    ];

    $jwt = JWT::encode($token, $this->jwtPrivateKey, 'RS256');

    $data = array(
      'message' => 'Login Successfull',
      'token'   => 'Bearer ' . $jwt
    );

    $response = $this->setJwtCookie($request, $response, $jwt);

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}