<?php
namespace App\Entity;

class UserEntity
{
  public string $id;
  public string $name;
  public string $username;
  public string $password;

  public function toJson(): object
  {
    return json_decode((string) json_encode(get_object_vars($this)), false);
  }
}