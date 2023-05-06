<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\WalletRepository;
use App\Entity\WalletEntity;

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;



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
    if(! isset($data->coin)) {
      throw new \Exception('The field "Coin Type" is required.', 400);
    }

    if($data->coin == 'btc') {
      $network    = Bitcoin::getNetwork();
      $random     = new Random();
      $pkFactory  = new PrivateKeyFactory();
      $privateKey = $pkFactory->generateCompressed($random);
      $publicKey  = $privateKey->getPublicKey();
      $address    = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());

      // $addrCreator = new AddressCreator();
      // $addr = $addrCreator->fromString("muzfkNWzWH9SgM6ufpeGbgkRKgZPMJWY3H");
      $p2pkh = $address->getScriptPubKey()->getHex();
      $hash  = hash('sha256', hex2bin($p2pkh));
      preg_match_all('/.{2}/', $hash, $matches, PREG_PATTERN_ORDER, 0);
      $scriptHash = implode('', array_reverse($matches[0]));
    } else {
      throw new \Exception('The Coin is not supported!', 400);
    }

    $now    = date('Y-m-d\TH:i:s.uP', time());
    $wallet = new WalletEntity();
    $wallet->uid        = $this->getUserId($request);
    $wallet->coin       = $data->coin;
    $wallet->network    = "testnet";
    $wallet->address    = $address->getAddress();
    $wallet->wif        = $privateKey->toWif(Bitcoin::getNetwork());
    $wallet->script_hash= $scriptHash;
    $wallet->created_at = $now;

    $wallet = $this->repository->createWallet($wallet);
    if($wallet <= 0) {
      throw new \Exception('Wallet create error!', 400);
    }

    $data = array(
      'message' => 'Wallet successfully created!',
      'coin'    => $data->coin,
      'network' => "testnet",
      'address' => $address->getAddress(),
      'walletid'=> $wallet
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}