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

use Nbobtc\Command\Command;
use Nbobtc\Http\Client;

Bitcoin::setNetwork(NetworkFactory::bitcoinTestnet());

$network = Bitcoin::getNetwork();

$unspendedTx = '714e0e55d55bd7a21ecd197a37e1ab577618568b8534b827e641f9d4c6aeb518';
$utxOutputIndex = 0;
$utxoPrivateKey = 'cR53LMqxfgtEBxbzAgEwW1vmTPMXAMiXPoNNhocs5tDVJFWXgx5c';
$utxoAmount = 5000;
$reciverPublicKey = 'mkrRJzD9QVaeynDk5JLstg3pYkksLuNnNN';
$fee = 1000;

$privFactory  = new PrivateKeyFactory();
$privKey      = $privFactory->fromWif($utxoPrivateKey);
$scriptPubKey = ScriptFactory::scriptPubKey()->payToPubKeyHash($privKey->getPubKeyHash());

$addrCreator = new AddressCreator();
$transaction = TransactionFactory::build()
    ->input($unspendedTx, $utxOutputIndex)
    ->payToAddress($utxoAmount - $fee, $addrCreator->fromString($reciverPublicKey))
    ->get();

echo $transaction->getHex() . "<br>";

// Script is P2SH | P2WSH | P2PKH
$p2wsh = new WitnessScript($scriptPubKey);
$p2sh  = new P2shScript($p2wsh);

$outpoint = new OutPoint(Buffer::hex($unspendedTx, 32), $utxOutputIndex);
$destinat = ScriptFactory::scriptPubKey()->payToPubKeyHash( $addrCreator->fromString($reciverPublicKey)->getHash() );
$unsigned = (new TxBuilder())
    ->spendOutPoint($outpoint)
    ->output($utxoAmount - $fee, $p2sh->getOutputScript())
    ->get();



// $outpoint     = new OutPoint(Buffer::hex($unspendedTx, 32), $utxOutputIndex);
// $destination  = ScriptFactory::scriptPubKey()->payToPubKeyHash( $addrCreator->fromString($reciverPublicKey)->getHash() );
// $transaction2 = TransactionFactory::build()
//     ->spendOutPoint($outpoint)
//     ->output($utxoAmount - $fee, $destination)
//     ->get();
// echo $transaction2->getHex() . '<BR><BR>';
// $witnessScript = new WitnessScript(ScriptFactory::scriptPubKey()->payToPubKeyHash($privKey->getPubKeyHash()));


$txOut = new TransactionOutput($utxoAmount - $fee, $scriptPubKey);

$signer = new Signer($unsigned);
$input  = $signer->input(0, $txOut);
$input->sign($privKey);

$signed = $signer->get();
echo $signed->getHex() . "<br><br><br>";


$client   = new Client('http://admin:admin@host.docker.internal:18332');
// $command  = new Command('sendrawtransaction', array($signed->getHex()));
// $response = $client->sendCommand($command);
// $contents = $response->getBody()->getContents();
// print_r($contents);
// echo "<br>";






//$command  = new Command('listunspent');
//$response = $client->sendCommand($command);
//$contents = $response->getBody()->getContents();
//$output   = json_decode($response->getBody()->getContents(), true);
$input    = array();
$amount   = 0.00004000;

$i=0;
// for(; isset($output["result"][$i]); $i++)
// {
//     $input[$i] = array(
//         "txid"=>$output["result"][$i]['txid'],
//         "vout"=>$output["result"][$i]['vout']
//     );
    
//     $amount += $output["result"][$i]['amount'];
// }

$input[0] = array(
    "txid" => $unspendedTx,
    "vout" => $utxOutputIndex
);


$fee = (int) ((($i+1) * 181 + 34 + 10) /1024) + 1;
$fee *= 0.00001000; //fee per kb

if($amount > $fee AND $fee > 0)
{
    echo "Amount:\t ".($amount - $fee)."<br>";
    echo "Fee: \t ".($fee)."<br>";

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










// use Denpa\Bitcoin\Client as BitcoinClient;

// $bitcoind = new BitcoinClient([
//     'scheme'        => 'http',
//     'host'          => 'host.docker.internal',
//     'port'          => 18332,
//     'user'          => 'admin',
//     'password'      => 'admin',
//     'ca'            => '/etc/ssl/ca-cert.pem',
//     'preserve_case' => false,
// ]);

// $unspent = $bitcoind->listunspent();
// $amount = 0;
// $i=0;
// for(; isset($unspent[$i]); $i++)
// {
//     $input[$i] = array(
//         "txid"=>$unspent[$i]['txid'],
//         "vout"=>$unspent[$i]['vout']
//     );
    
//     $amount += $unspent[$i]['amount'];
// }

// $fee = (int) ((($i+1) * 181 + 34 + 10) /1024) + 1;
// $fee *= 0.0001; //fee per kb

// if($amount > $fee AND $fee > 0)
// {
//     echo "Amount:\t ".($amount - $fee)."\n";
//     echo "Fee: \t ".($fee)."\n";
//     $output = array("mmGkwmzwVQj2sRsBvjt3mKRuE7R9EXMSHG" => ($amount - $fee));
//     print_r($input);
//     echo "-------";
//     print_r($output);
//     $tx = $bitcoind->createrawtransaction($input, $output);
//     $signed = $bitcoind->signrawtransactionwithkey($tx->result(), ["cQgCXfF6y8H4rswHR5RWF91QWKeJUhC8ZLb3G6v76ESU4K6y4nG8","cTP72DYYU7RsJEg5sAzsoFGof2DFE4m2tAGw16dw7uQFSC469ow3"]);
//     if(!empty($signed->get("hex"))) {
//         $res = $bitcoind->sendrawtransaction($signed->get("hex"));
//         print_r($res);
//     } else {
//       echo "failed";
//     }
//     echo "\n";
// }

// print_r($block);

// use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;

// $bitcoinECDSA = new BitcoinECDSA();
// $bitcoinECDSA->setNetworkPrefix('6f');
// // $bitcoinECDSA->generateRandomPrivateKey();
// $bitcoinECDSA->setPrivateKey("ad0ef5b050953cea3379f4eb8a8fdc65d47207f54d2989e23f6c75db1039e87c");
// $private = $bitcoinECDSA->getPrivateKey();
// $wif = $bitcoinECDSA->getWif();
// $address = $bitcoinECDSA->getAddress();
// $address = $bitcoinECDSA->getAddress();
// $getP2SHAddress = $bitcoinECDSA->getP2SHAddress();
// $getUncompressedP2SHAddress = $bitcoinECDSA->getUncompressedP2SHAddress();
// $getPubKey = $bitcoinECDSA->getPubKey();
// $getUncompressedPubKey = $bitcoinECDSA->getUncompressedPubKey();
// echo "Address: " . $address . PHP_EOL . "<br>";
// echo "PrivateKey: " . $private . PHP_EOL . "<br>";
// echo "WIF : " . $wif . PHP_EOL . "<br>";
// echo "getP2SHAddress : " . $getP2SHAddress . PHP_EOL . "<br>";
// echo "getUncompressedP2SHAddress : " . $getUncompressedP2SHAddress . PHP_EOL . "<br>";
// echo "getPubKey : " . $getPubKey . PHP_EOL . "<br>";
// echo "getUncompressedPubKey : " . $getUncompressedPubKey . PHP_EOL . "<br>";

// //import wif
// $bitcoinECDSA = new BitcoinECDSA();
// if($bitcoinECDSA->validateWifKey($wif)) {
//     $bitcoinECDSA->setPrivateKeyWithWif($wif);
//     $address = $bitcoinECDSA->getAddress();
//     echo "imported address : " . $address . PHP_EOL;
// } else {
//     echo "invalid WIF key" . PHP_EOL;
// }

// echo "<br>";



// // Defining host, port, and timeout
// $host = 'electrumx';
// $port = 50002;
// $timeout = 30;
 
// // Setting context options
// $context = stream_context_create();
// stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
// stream_context_set_option($context, 'ssl', 'verify_peer_name', false);
 
// // JSON query for fee estimation in 5 blocks
// $data = "16LU1w8AbU9QCfk8MjNW4kqVgmAAPUvAtd";
// $scripthash = hash('sha256', hex2bin(hash('sha256', $data)));
// $query='{"id": "blk", "method": "blockchain.scripthash.get_balance", "params":["'.$scripthash.'"]}';
// //$query='{"id": "blk", "method": "server.features"}';
// if ($socket = stream_socket_client('ssl://'.$host.':'.$port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context)) {
//     fwrite($socket, $query."\n");
//     $value=fread($socket,10240);
//     $result=json_decode($value);
//     print_r($result);
//     fclose($socket);
// } else {
//    echo "ERROR: $errno - $errstr\n";
// }

