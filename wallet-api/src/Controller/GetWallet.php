<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;
use App\Utils\Jsonrpc;

class GetWallet extends BaseController
{
  private WalletRepository $repository;

  public function __construct()
  {
    $this->repository = new WalletRepository();
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $userId  = $this->getUserId($request);
    $wallets = $this->repository->getWallet($userId);

    // Get wallet balance from electrumx
    $jsonrpc = new Jsonrpc();
    foreach ($wallets as $key => $value) {
      $balance = $jsonrpc->call("blockchain.scripthash.get_balance", array($value->script_hash));
      $wallets[$key]->balance = $balance;
      unset($wallets[$key]->script_hash);
    }
    $jsonrpc->close();

    $data = array(
      'wallets' => $wallets
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}