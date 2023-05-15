<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;
use App\Libs\BitcoinLib;


class CreateWallet extends BaseController
{
  private WalletRepository $repository;

  public function __construct()
  {
    $this->repository = new WalletRepository();
  }

  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();
    $data = json_decode(json_encode($data), false);
    if(! isset($data->currency)) {
      throw new \Exception('The field "Currency" is required.', 400);
    }

    if($data->currency == 'btc') {
      $bitcoin = (new BitcoinLib())->newAddress();
    } else {
      throw new \Exception('The Currency is not supported!', 400);
    }

    $now    = date('Y-m-d\TH:i:s.uP', time());
    $wallet = new WalletEntity();
    $wallet->uid        = $this->getUserId($request);
    $wallet->currency   = $data->currency;
    $wallet->network    = $bitcoin["network"];
    $wallet->address    = $bitcoin["address"];
    $wallet->wif        = $bitcoin["wif"];
    $wallet->created_at = $now;

    $wid = $this->repository->createWallet($wallet);
    if($wid <= 0) {
      throw new \Exception('Wallet create error!', 400);
    }

    $data = array(
      'message'  => 'Wallet successfully created!',
      'currency' => $data->currency,
      'network'  => $bitcoin["network"],
      'address'  => $bitcoin["address"],
      'wid'      => md5($wid)
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}