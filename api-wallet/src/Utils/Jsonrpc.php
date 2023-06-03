<?php

namespace App\Utils;

use App\Utils\Settings;

class Jsonrpc 
{
  public function __construct()
  {
    $settings = Settings::getSettings();

    $context = stream_context_create();
    if ($settings->elx->protocol == 'ssl') {
      stream_context_set_option($context, 'ssl', 'allow_self_signed', true);
      stream_context_set_option($context, 'ssl', 'verify_peer_name', false);      
    }

    $this->socket = @stream_socket_client(
      $settings->elx->protocol . '://' . $settings->elx->host . ':' . $settings->elx->port, 
      $errno, 
      $errstr, 
      30, 
      STREAM_CLIENT_CONNECT, 
      $context
    );

    if (false === $this->socket) {
      throw new \Exception('RPC server connection error!', 400);
    }
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
      fwrite($this->socket, json_encode($query));
      fwrite($this->socket, "\n");
      fflush($this->socket);

      // $response = fgets($this->socket);
      // if ($response === false) {
      //     throw new \Exception('Connection to failed');
      // }
      // $result = json_decode($response, true);
      // if(isset($result['error'])){
      //     throw new \Exception($result['error']['message']);
      // }
      
      // return $result['result'];

      $value  = fread($this->socket, 10240);
      $result = json_decode($value, true);
      if (! isset($result["result"])) {
        $error = isset($result["error"]) ? json_encode($result["error"]) : "Unknown!";
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
