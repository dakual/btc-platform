<?php

namespace App\Repository;

use App\Utils\Settings;
use \PDO;

abstract class BaseRepository
{
  public $settings;
  public $database;

  public function __construct()
  {
    $this->settings = Settings::getSettings();

    try {
      $connection_str = "mysql:host={$this->settings->db->host};dbname={$this->settings->db->name}";
      $this->database = new PDO($connection_str, $this->settings->db->user, $this->settings->db->pass);
      $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(\Exception $ex) {
      throw new \Exception(
        'Database failed: ' . $ex->getMessage(), 400
      );
    }
  }

  protected function getDb(): PDO
  {
      return $this->database;
  }
}