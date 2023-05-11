<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;

class DefaultController extends BaseController
{
    private const API_VERSION = '1.0.0';

    public function getMain(Request $request, Response $response): Response
    {
      $status = [
        'message'   => 'Wallet Api v1.0',
        'version'   => self::API_VERSION,
        'status'    => 'healthy',
        'timestamp' => time(),
      ];

      return $this->jsonResponse($response, 'success', $status, 200);
    }
}