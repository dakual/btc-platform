<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Libs\BitcoinLib;


class GetWithdraw extends BaseController
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

    if(! isset($params["currency"])) {
      throw new \Exception('The field "Currency" is required.', 400);
    }

    if($params["currency"] == 'btc') {
      $bitcoinLib  = new BitcoinLib();
      $withdrawals = $this->repository->getWithdrawals(
        $params["currency"], 
        $bitcoinLib->getNetwork(), 
        $userId
      );
    } else {
      throw new \Exception('The Currency is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $withdrawals, 200);
  }
}