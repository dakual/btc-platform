<?php
namespace App\Entity;

class WalletEntity
{
  public string $wid;
  public string $uid;
  public string $coin;
  public string $network;
  public string $address;
  public string $wif;
  public string $script_hash;
  public string $created_at;

  public function toJson(): object
  {
    return json_decode((string) json_encode(get_object_vars($this)), false);
  }
}