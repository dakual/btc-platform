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
    $userId       = $this->getUserId($request);
    $data         = (array) $request->getParsedBody();
    $data         = json_decode(json_encode($data), false);
    $review       = isset($data->action) && $data->action == 'send' ? false : true;
    $responseData = array();

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
      
      $transaction = $bitcoinLib->createTx(
        $wallets, 
        $data->address, 
        $data->amount
      );

      $responseData['uid']         = $wallets['uid'];
      $responseData['currency']    = $wallets['currency'];
      $responseData['network']     = $wallets['network'];
      $responseData['action']      = ($review) ? 'review' : 'send';
      $responseData['transaction'] = $transaction;
      
      if (! $review) {
        $jsonrpc = new Jsonrpc();
        $tx_resp = $jsonrpc->call("blockchain.transaction.broadcast", array($transaction["tx_hex"]));
        $jsonrpc->close();
        
        if (! isset($tx_resp["result"]) || strcmp($tx_resp["result"], $transaction["tx_id"]) !== 0) {
          throw new \Exception('RPC! Something went wrong!', 400);
        }

        $this->repository->saveWithdraw($responseData);
      }
    } else {
      throw new \Exception('The currency is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $responseData, 200);
  }


}