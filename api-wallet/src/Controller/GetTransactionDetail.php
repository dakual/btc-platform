<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Utils\Jsonrpc;
use App\Libs\BitcoinLib;


use BitWasp\Bitcoin\Block\BlockFactory;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Block\BlockHeaderFactory;

use BitWasp\Bitcoin\Serializer\Block\BlockHeaderSerializer;
use BitWasp\Bitcoin\Serializer\Block\FilteredBlockSerializer;
use BitWasp\Bitcoin\Serializer\Block\PartialMerkleTreeSerializer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Transaction\OutPoint;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Bitcoin\Utxo\Utxo;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;

class GetTransactionDetail extends BaseController
{
  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $userId = $this->getUserId($request);
    $params = $request->getQueryParams();
    $txId   = $args['id'];

    if(! isset($txId)) {
      throw new \Exception('The field "Tx ID" is required.', 400);
    }

    $jsonrpc = new Jsonrpc();
    // $data = $jsonrpc->call("blockchain.transaction.get", array($txId, true));
    // $data = $data["result"];


    $data = $jsonrpc->call("blockchain.scripthash.get_history", array($txId));
    foreach($data["result"] as $tx) {
      echo $tx["tx_hash"];
    }

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}