<?php
namespace App\Utils;

class Jsonrpc {
  
  private $host;
  private $port;
  private $socket;

  public function __construct()
  {
    $this->host = 'electrum.blockstream.info';
    $this->port = 60001;

    $context = stream_context_create();
    // stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
    // stream_context_set_option($context, 'ssl', 'verify_peer_name', false);

    $this->socket = stream_socket_client('tcp://'.$this->host.':'.$this->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
  }

  public function call(string $method, array $params): array
  {
    $result = null;
    $query  = array(
      "jsonrpc" => "2.0",
      "id"      => time(),
      "method"  => $method,
      "params"  => $params
    );

    if ($this->socket)
    {
      fwrite($this->socket, json_encode($query)."\n");
      $value  = fread($this->socket, 10240);
      $result = json_decode($value, true);
      if (! isset($result["result"])) {
        $error = isset($result["error"]) ? $result["error"] : "Unknown!";
        throw new \Exception("Oops! RPC Error: " . $error, 400);
      }
      unset($result["id"]);
      unset($result["jsonrpc"]);
    } else {
      $result = array("ERROR" => "$errno - $errstr");
    }

    return $result;
  }

  public function close(): void {
    fclose($this->socket);
  }
}
