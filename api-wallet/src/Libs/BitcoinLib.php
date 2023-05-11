<?php
namespace App\Libs;

use App\Utils\Settings;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Bitcoin\Script\P2shScript;




class BitcoinLib
{
  private $settings;
  private $network;

  public function __construct()
  {
    $this->settings = Settings::getSettings();
    if($this->settings->btc->network == 'testnet') {
      Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());
    }
    
    $this->network = Bitcoin::getNetwork(); 
  }

  public function newAddress() 
  {
    $ecAdapter  = Bitcoin::getEcAdapter();
    $random     = new Random();
    $pkFactory  = new PrivateKeyFactory($ecAdapter);
    $privateKey = $pkFactory->generateCompressed($random);
    $publicKey  = $privateKey->getPublicKey();
    $address    = new PayToPubKeyHashAddress($publicKey->getPubKeyHash());
    $network    = self::getNetwork();

    return [
      "network" => $network,
      "address" => $address->getAddress(),
      "wif"     => $privateKey->toWif($this->network)
    ];
  }

  public function toScriptHash(string $address): string 
  {
    $addrCreator = new AddressCreator();
    $addr  = $addrCreator->fromString($address, $this->network);
    $p2pkh = $addr->getScriptPubKey()->getHex();
    $hash  = hash('sha256', hex2bin($p2pkh));
    preg_match_all('/.{2}/', $hash, $matches, PREG_PATTERN_ORDER, 0);
    
    return implode('', array_reverse($matches[0]));
  }

  public function getNetwork(): string 
  {
    return (new \ReflectionClass($this->network))->getShortName() == 'BitcoinTestnet' ? 'testnet' : 'mainnet';
  }

  public function createTx(array $wallet, string $address, int $amount): array {
    $network     = Bitcoin::getNetwork();    
    $ecAdapter   = Bitcoin::getEcAdapter();
    $addrCreator = new AddressCreator($ecAdapter);
    $privFactory = new PrivateKeyFactory();
    $builder     = TransactionFactory::build()->version(2)->locktime(0);
        
    $feeRate     = 1;
    $inputCount  = 0;
    $outputCount = 0;
    $unspentList = [];
    $wallet      = json_decode(json_encode($wallet), true);
    $wallets     = $wallet["wallets"];
    $userId      = $wallet["uid"];

    try {
      $address = $addrCreator->fromString($address, $network);
      uasort($wallets, function($item1, $item2){
        return strtotime($item2['created_at']) - strtotime($item1['created_at']);
      });
      $remainingAmountAddress = $wallets[0]["address"];
      $remainingAmountAddress = $addrCreator->fromString($remainingAmountAddress, $network);
    } catch (\Exception $e) {
      throw new \Exception("Oops! address wrong", 400);
    }

    // uspent list
    foreach ($wallets as $walletItem) {
      $unspents = (array) $walletItem['unspent'];
      foreach ($unspents as $unspent) {
        $unspent["address"] = $walletItem["address"];
        $unspent["wif"] = $walletItem["wif"];
        $unspentList[]  = (array) $unspent;
      }
    }

    // calculate total unspent amount
    $totalUnspentAmount  = 0;
    $totalUnspentAmount += array_sum(array_column($unspentList, 'value'));
    if ($totalUnspentAmount < $amount) {
      throw new \Exception("Oops! you don't have enough money to spend", 400);
    }

    // sort unspent list
    usort($unspentList, function ($item1, $item2) {
      $item1 = (array) $item1;
      $item2 = (array) $item2;
      return $item1['value'] <=> $item2['value'];
    });


    // select unspent from list
    $selectedUnspents = [];
    foreach ($unspentList as $key => $unspent) {
      $unspent = (array) $unspent;
      if ($unspent['value'] >= $amount) {
        $selectedUnspents[] = $unspent;
        $totalUnspentAmount = intval($unspent['value']);
        $inputCount         = 1;

        break;
      }
    }
    
    if (empty($selectedUnspents)) {
      usort($unspentList, function ($item1, $item2) {
        $item1 = (array) $item1;
        $item2 = (array) $item2;
        return $item2['value'] <=> $item1['value'];
      });

      $selectedAmounts = 0;
      foreach ($unspentList as $unspent) {
        $unspent            = (array) $unspent;
        $selectedUnspents[] = $unspent;
        $selectedAmounts    = intval($unspent['value'] + $selectedAmounts);
        $inputCount++;
        if ($selectedAmounts >= $amount) {
          break;
        }
      }
      $totalUnspentAmount = $selectedAmounts;
    }
    $unspentList = $selectedUnspents;

    // set output count
    if ($totalUnspentAmount == $amount) {
      $outputCount = 1;
    } elseif ($totalUnspentAmount > $amount) {
      $outputCount = 2;
    }

    // calculate fee
    $fee = intval(($inputCount * 148 + $outputCount * 34 + 10) * $feeRate);
    if ($fee >= $amount) {
      throw new \Exception("Oops! the fee is more than your amount!", 400);
    }
    
    $userWillReceive  = $amount - $fee;
    $totalExtraAmount = $totalUnspentAmount - $amount;
    
    // prepare input
    $inputIndex = 0;
    foreach ($unspentList as &$unspent) {
      $builder->input($unspent["tx_hash"], $unspent["tx_pos"]);
      $unspent["input_index"] = $inputIndex;
      $inputIndex++;
    }

    // prepare output
    $builder->payToAddress($userWillReceive, $address);
    if ($outputCount > 1) {
      $builder->payToAddress($totalExtraAmount, $remainingAmountAddress);
    }

    // signing
    $unsigned = $builder->get();
    $signer   = new Signer($unsigned, $ecAdapter);
    foreach ($unspentList as $unspent) {
      $prKey = $privFactory->fromWif($unspent["wif"], $network);
      $p2pkh = ScriptFactory::scriptPubKey()->payToPubKeyHash($prKey->getPubKeyHash());
      $txOut = new TransactionOutput($unspent["value"], $p2pkh);
      $signer->sign($unspent["input_index"], $prKey, $txOut);
    }

    $signed = $signer->get();

    return [
      "uid"          => $wallet["uid"],
      "coin"         => $wallet["coin"],
      "network"      => $wallet["network"],
      "transaction"  => array(
          "address"      => $address->getAddress(),
          "input_count"  => $inputCount,
          "output_count" => $outputCount,
          "fee"          => $fee,
          "fee_rate"     => $feeRate,
          "unspent"      => $totalUnspentAmount,
          "amount"       => $userWillReceive,
          "residue"      => $totalExtraAmount,
          "tx_id"        => $signed->getTxId()->getHex(),
          "tx_hex"       => $signed->getHex(),
          "created_at"   => date('Y-m-d\TH:i:s.uP', time()),
          "status"       => "pending"        
      )
    ];
  }

}
