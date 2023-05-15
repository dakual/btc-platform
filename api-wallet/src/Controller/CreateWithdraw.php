<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;
use App\Utils\Jsonrpc;
use App\Libs\BitcoinLib;



class CreateWithdraw extends BaseController
{
  private WalletRepository $repository;

  public function __construct()
  {
    $this->repository = new WalletRepository();
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $userId = $this->getUserId($request);
    $data   = (array) $request->getParsedBody();
    $data   = json_decode(json_encode($data), false);

    if(! isset($data->currency)) {
      throw new \Exception('The field "Currency" is required.', 400);
    }

    if(! isset($data->address)) {
      throw new \Exception('The field "Address" is required.', 400);
    }

    if(! isset($data->amount) || ! is_numeric($data->amount)) {
      throw new \Exception('The field "Amount" is required.', 400);
    }

    $bitcoinLib = new BitcoinLib();
    if($data->currency == 'btc') {
      $wallets = $this->repository->getWallet($data->currency, $bitcoinLib->getNetwork(), $userId);
      
      // Get unspent from electrumx
      $jsonrpc = new Jsonrpc();
      foreach ($wallets["wallets"] as $key => $value) {
        $scriptHash = $bitcoinLib->toScriptHash($value->address);
        $unspent    = $jsonrpc->call("blockchain.scripthash.listunspent", array($scriptHash));
        
        $wallets["wallets"][$key]->unspent = $unspent["result"];
      }
      $jsonrpc->close();
    } else {
      throw new \Exception('The currency is not supported!', 400);
    }

    $data = $bitcoinLib->createTx($wallets, $data->address, $data->amount);
    if(count($data) <= 0) {
      throw new \Exception('Opps! Something went wrong!', 400);
    }

    try {
      $this->repository->saveWithdraw($data);
    } catch(\Exception $ex) {
      throw new \Exception('Opps! Transaction has already exist!', 400);
    }
    
    $data["transaction"] = array_merge(
      array('tid' => $data["transaction"]["tx_id"]), 
      $data["transaction"]
    );

    unset($data["transaction"]["tx_id"]);
    unset($data["transaction"]["tx_hex"]);
    unset($data["transaction"]["input_count"]);
    unset($data["transaction"]["output_count"]);
    unset($data["transaction"]["fee_rate"]);
    unset($data["transaction"]["unspent"]);
    unset($data["transaction"]["residue"]);

    return $this->jsonResponse($response, 'success', $data, 200);
  }


}