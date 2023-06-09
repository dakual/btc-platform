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

    if(! isset($params["currency"])) {
      throw new \Exception('The field "Currency" is required.', 400);
    }

    $data = array();
    if($params["currency"] == 'btc') {
      $bitcoinLib = new BitcoinLib();
      $wallets    = $this->repository->getWallet($params["currency"], $bitcoinLib->getNetwork(), $userId);

      // Get wallet balance from electrumx
      $totalConfirmed   = 0;
      $totalUnconfirmed = 0;

      $jsonrpc = new Jsonrpc();
      foreach ($wallets["wallets"] as $key => $value) {
        $scriptHash = $bitcoinLib->toScriptHash($value->address);
        $balance    = $jsonrpc->call("blockchain.scripthash.get_balance", array($scriptHash));

        $totalConfirmed   += (int) $balance["result"]["confirmed"];
        $totalUnconfirmed += (int) $balance["result"]["unconfirmed"];
        $wallets["wallets"][$key]->balance = $balance["result"];
        // $wallets["wallets"][$key]->scripthash = $scriptHash; // will removed

        unset($wallets["wallets"][$key]->wif);
      }
      $jsonrpc->close();

      $wallets["totalConfirmed"]   = $totalConfirmed;
      $wallets["totalUnconfirmed"] = $totalUnconfirmed;
    } else {
      throw new \Exception('The Currency is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $wallets, 200);
  }
}