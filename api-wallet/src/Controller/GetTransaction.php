<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Repository\WalletRepository;
use App\Controller\BaseController;
use App\Utils\Jsonrpc;
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

    if(! isset($params["currency"])) {
      throw new \Exception('The field "Currency" is required.', 400);
    }

    
    if($params["currency"] == 'btc') {
      $jsonrpc    = new Jsonrpc();
      $bitcoinLib = new BitcoinLib();
      $wallets    = $this->repository->getWallet($params["currency"], $bitcoinLib->getNetwork(), $userId);
      $currentHeight = $jsonrpc->call("blockchain.headers.subscribe", []);
      $currentHeight = (int) $currentHeight["result"]["height"];
      $transactions  = array();

      // Get Transactions by wallet
      foreach ($wallets["wallets"] as $key => $value) {
        $scriptHash   = $bitcoinLib->toScriptHash($value->address);
        $rpcResult    = $jsonrpc->call("blockchain.scripthash.get_history", array($scriptHash));
        $rpcResult    = $rpcResult["result"];
        $transactions = array_merge($transactions, $rpcResult);
      }

      // Remove duplicate transactions
      $ids = array_column($transactions, 'tx_hash');
      $ids = array_unique($ids);
      $transactions = array_filter($transactions, function ($key, $value) use ($ids) {
          return in_array($value, array_keys($ids));
      }, ARRAY_FILTER_USE_BOTH);

      // Sort transactions
      usort($transactions, function ($item1, $item2) {
        return $item1['height'] <=> $item2['height'];
      });

      // Get transaction details
      foreach($transactions as $k => $item) {
        $hex = $jsonrpc->call("blockchain.transaction.get", array($item["tx_hash"], false));
        $hex = $hex["result"];
        $height = (int)$item["height"];
        $transactions[$k]["confirmation"] = $height > 0 ? $currentHeight - $height : 0;
        $transactions[$k]["hex"]          = $hex;
        $transactions[$k]["decoded"]      = $bitcoinLib->decodeRaw($hex, $verbode = false);
      }

      $jsonrpc->close();

      // foreach($txList as $tx) {
      //   $data = [
      //     "tid" => $tx["tx_hash"],
      //     "uid" => $wallets["uid"],
      //     "currency" => $wallets["currency"],
      //     "network"  => $wallets["network"]
      //   ];

      //   try {
      //     $this->repository->saveTransaction($data);
      //   } catch(\Exception $ex) { }
      // }

      $data = [
        "uid"      => $userId,
        "currency" => $params["currency"],
        "network"  => $bitcoinLib->getNetwork(),
        "total"    => count($transactions),
        "transactions" => $transactions
      ];

    } else {
      throw new \Exception('The Currency is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}