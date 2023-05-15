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


class Withdraw extends BaseController
{
  private WalletRepository $repository;

  public function __construct()
  {
    $this->repository = new WalletRepository();
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $userId = $this->getUserId($request);
    $params = (array) $request->getParsedBody();
    $params = json_decode(json_encode($params), false);
    $data   = array();

    if(! isset($params->txid)) {
      throw new \Exception('The field "TX ID" is required.', 400);
    }

    $tx = $this->repository->getWithdraw($userId, $params->txid);
    if($tx["status"] != 'pending') {
      throw new \Exception('Transaction is expired!', 400);
    }

    if($tx["currency"] == 'btc') {
      try {
        $jsonrpc = new Jsonrpc();
        $tx_resp = $jsonrpc->call("blockchain.transaction.broadcast", array($tx["hex"]));
        $jsonrpc->close();
        
        if (strcmp($tx_resp["result"], $tx["tid"]) !== 0) {
          throw new \Exception('Opps! Something went wrong!', 400);
        }

        $this->repository->updateWithdraw($userId, $params->txid, 'completed');
        $data = [
          "result" => "success",
          "txid"   => $tx_resp["result"]
        ];
      } catch(\Exception $ex) {
        $this->repository->updateWithdraw($userId, $params->txid, 'faild');
        throw new \Exception($ex->getMessage(), 400);
      }
    } else {
      throw new \Exception('The Coin is not supported!', 400);
    }

    return $this->jsonResponse($response, 'success', $data, 200);
  }


}