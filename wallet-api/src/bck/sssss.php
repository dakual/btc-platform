<?php
namespace App;

require_once('../vendor/autoload.php');

use BitWasp\Bitcoin\Address\PayToPubKeyHashAddress;
use BitWasp\Bitcoin\Address\ScriptHashAddress;
use BitWasp\Bitcoin\Address\SegwitAddress;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\WitnessProgram;
use BitWasp\Bitcoin\Network\NetworkFactory;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Transaction\TransactionFactory;
use BitWasp\Bitcoin\Transaction\OutPoint;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Transaction\TransactionOutput;
use BitWasp\Bitcoin\Transaction\Factory\Signer;
use BitWasp\Buffertools\Buffer;
use BitWasp\Bitcoin\Crypto\EcAdapter\Key\PrivateKeyInterface;
use BitWasp\Bitcoin\Script\P2shScript;
use BitWasp\Bitcoin\Transaction\Factory\TxBuilder;
use BitWasp\Bitcoin\Transaction\SignatureHash\SigHash;
use BitWasp\Bitcoin\Script\WitnessScript;
use BitWasp\Bitcoin\Transaction\Factory\SignData;
use BitWasp\Bitcoin\Script\Interpreter\InterpreterInterface as I;

use Nbobtc\Command\Command;
use Nbobtc\Http\Client;

Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());

// Variables
$unspendedTx    = 'ecd8327af1309e5a4cdd79a98b4939dcc5f189d7c3c5e256ca046338e53cf683';
$utxOutputIndex = 0;
$utxoPrivateKey = 'cQe27KSkCYJuiRM7xWnob3kQYNmkRNmF47yH7D9B5BDP9B67EuPw';//'cR53LMqxfgtEBxbzAgEwW1vmTPMXAMiXPoNNhocs5tDVJFWXgx5c';
$utxoAmount     = 3000;
$reciverPublicKey = 'mkrRJzD9QVaeynDk5JLstg3pYkksLuNnNN';
$fee            = 1000;


// Bitcoind client
$client = new Client('http://admin:admin@host.docker.internal:18332');

// Private Key
$privFactory  = new PrivateKeyFactory();
$privKey      = $privFactory->fromWif($utxoPrivateKey);
$scriptPubKey = ScriptFactory::scriptPubKey()->payToPubKeyHash($privKey->getPubKeyHash());
$redeemScript = ScriptFactory::scriptPubKey()->p2wkh($privKey->getPubKeyHash());

// Transaction
$addrCreator = new AddressCreator();
$destination = ScriptFactory::scriptPubKey()->payToPubKeyHash( $addrCreator->fromString($reciverPublicKey)->getHash() );
$outpoint    = new OutPoint(Buffer::hex($unspendedTx, 32), $utxOutputIndex);
$transaction = TransactionFactory::build()
    ->version(2)
    ->spendOutPoint($outpoint)
    ->output($utxoAmount - $fee, $destination)
    ->get();

echo $transaction->getHex() . '<BR>';


// $outpoint    = new OutPoint(Buffer::hex($unspendedTx, 32), $utxOutputIndex);
// $destination = new PayToPubKeyHashAddress($addrCreator->fromString($reciverPublicKey)->getHash());
// $transaction = (new TxBuilder())
//     ->version(2)
//     ->spendOutPoint($outpoint)
//     ->payToAddress($utxoAmount - $fee, $destination)
//     ->get();


// Sign transaction
$txOut    = new TransactionOutput($utxoAmount, $scriptPubKey);
$signer   = new Signer($transaction);
$input    = $signer->input(0, $txOut);
$input->sign($privKey);

$signed = $signer->get();
echo $signed->getHex() . "<br><br><br>";


$command  = new Command('sendrawtransaction', array($signed->getHex()));
$response = $client->sendCommand($command);
$contents = $response->getBody()->getContents();
print_r($contents);
echo "<br>";





$input    = array();
$amount   = 0.00004000;
$input[0] = array(
    "txid" => $unspendedTx,
    "vout" => $utxOutputIndex
);

if($amount > 0)
{
    $output   = array($reciverPublicKey => ($amount));
    $response = $client->sendCommand(new Command('createrawtransaction', array($input, $output)));
    $response = json_decode($response->getBody()->getContents(), true);

    echo $response["result"] . "<br>";

    $response = $client->sendCommand(new Command('signrawtransactionwithkey', array($response["result"], [$utxoPrivateKey])));
    $response = json_decode($response->getBody()->getContents(), true);

    echo $response["result"]["hex"] . "<br><br>";

    // if(!empty($response["result"]["hex"])) {
    //     $response = $client->sendCommand(new Command('sendrawtransaction', array($response["result"]["hex"])));
    //     $response = json_decode($response->getBody()->getContents(), true);      
    //     print_r($response);
    // } else {
    //   echo "failed";
    // }
}




