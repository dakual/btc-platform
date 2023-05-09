<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;
use App\Utils\Jsonrpc;
use App\Utils\Settings;
use App\Libs\BitcoinLib;


class GetWallet extends BaseController
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
      $wallets    = $this->repository->getWallet($params["coin"], $bitcoinLib->getNetwork(), $userId);

      // Get wallet balance from electrumx
      $totalConfirmed  = 0;
      $totalUnonfirmed = 0;
      $jsonrpc = new Jsonrpc();
      foreach ($wallets["wallets"] as $key => $value) {
        $scriptHash = $bitcoinLib->toScriptHash($value->address);
        $balance    = $jsonrpc->call("blockchain.scripthash.get_balance", array($scriptHash));

        $totalConfirmed  += (int) $balance["result"]["confirmed"];
        $totalUnonfirmed += (int) $balance["result"]["unconfirmed"];
        $wallets["wallets"][$key]->sh = $scriptHash;
        $wallets["wallets"][$key]->balance = $balance["result"];

        unset($wallets["wallets"][$key]->wif);
      }
      $jsonrpc->close();
    } else {
      throw new \Exception('The Coin is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $wallets, 200);
  }
}