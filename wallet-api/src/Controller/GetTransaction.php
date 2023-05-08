<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Libs\BitcoinLib;


class GetTransaction extends BaseController
{
  private WalletRepository $repository;

  public function __construct()
  {
    $this->repository = new WalletRepository();
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $userId = $this->getUserId($request);
    $params = $request->getQueryParams();

    if(! isset($params["coin"])) {
      throw new \Exception('The field "Coin Type" is required.', 400);
    }

    $data = array();
    if($params["coin"] == 'btc') {
      $bitcoinLib = new BitcoinLib();
      $allTx      = $this->repository->getAllTx($params["coin"], $bitcoinLib->getNetwork(), $userId);
    } else {
      throw new \Exception('The Coin is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $allTx, 200);
  }
}