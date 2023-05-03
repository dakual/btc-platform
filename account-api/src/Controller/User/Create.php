<?php
namespace App\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\UserRepository;
use App\Entity\UserEntity;

class Create extends BaseController
{
  private UserRepository $repository;

  public function __construct()
  {
    $this->repository = new UserRepository();
  }
  
  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();
    $user = $this->validateUserData($data);
    $user = $this->repository->createUser($user);

    $data = array(
      'message' => 'Create Successfull',
      'user'    => $user->id
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }

  private function validateUserData(array $data): UserEntity
  {
    if (! isset($data["username"])) {
      throw new \App\Exception\Auth('The field "username" is required.', 400);
    }
    if (! isset($data["password"])) {
      throw new \App\Exception\Auth('The field "password" is required.', 400);
    }
    if (! isset($data["name"])) {
      throw new \App\Exception\Auth('The field "name" is required.', 400);
    }

    $this->repository->checkUserByUsername($data["username"]);

    $hash = password_hash($data["password"], PASSWORD_BCRYPT);

    $newUser = new UserEntity();
    $newUser->name     = $data["name"];
    $newUser->username = $data["username"];
    $newUser->password = $hash;

    return $newUser;
  }
}