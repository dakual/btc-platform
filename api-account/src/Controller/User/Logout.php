<?php
namespace App\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;


class Logout extends BaseController
{
  
  public function __invoke(Request $request, Response $response): Response
  {
    $data = array(
      'message' => 'Logout Successfull'
    );

    $response = $this->setJwtCookie($request, $response, '');

    return $this->jsonResponse($response, 'success', $data, 200);
  }

}