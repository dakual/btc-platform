<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;
use App\Utils\Jsonrpc;
use App\Libs\BitcoinLib;

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;



class CreateTransaction extends BaseController
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

    if(! isset($data->coin)) {
      throw new \Exception('The field "Coin Type" is required.', 400);
    }

    if(! isset($data->address)) {
      throw new \Exception('The field "Address" is required.', 400);
    }

    if(! isset($data->amount) || ! is_numeric($data->amount)) {
      throw new \Exception('The field "Amount" is required.', 400);
    }

    $bitcoinLib = new BitcoinLib();
    if($data->coin == 'btc') {
      $wallets = $this->repository->getWallet($data->coin, $bitcoinLib->getNetwork(), $userId);
      
      // Get unspent from electrumx
      $jsonrpc = new Jsonrpc();
      foreach ($wallets["wallets"] as $key => $value) {
        $scriptHash = $bitcoinLib->toScriptHash($value->address);
        $unspent    = $jsonrpc->call("blockchain.scripthash.listunspent", array($scriptHash));
        
        $wallets["wallets"][$key]->unspent = $unspent["result"];
      }
      $jsonrpc->close();
    } else {
      throw new \Exception('The Coin is not supported!', 400);
    }

    $data = $bitcoinLib->createTx($wallets, $data->address, $data->amount);
    if(count($data) <= 0) {
      throw new \Exception('Opps! Something went wrong!', 400);
    }

    $txid = $this->repository->saveTx($data);
    $data["transaction"] = array_merge(
      array('tid' => md5($txid)), 
      $data["transaction"]
    );

    unset($data["tx_id"]);
    unset($data["tx_hex"]);

    return $this->jsonResponse($response, 'success', $data, 200);
  }


}