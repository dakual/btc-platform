<?php
namespace App\Repository;

use App\Entity\WalletEntity;

class WalletRepository extends BaseRepository
{
  public function getWallet(string $userId): Array
  {
    $query = '
        SELECT w.id, w.uid, w.coin, w.network, w.address, w.script_hash, w.created_at FROM `wallets` w WHERE w.uid = :uid
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $userId);
    $statement->execute();

    $wallets = (array) $statement->fetchAll(\PDO::FETCH_CLASS, WalletEntity::class) ?: [];
    if (! $wallets) {
        throw new \Exception('Wallet not found.', 404);
    }

    return $wallets;
  }

  public function createWallet(WalletEntity $wallet): int
  {
    $wallet_query = '
        INSERT INTO `wallets`
          (`uid`, `coin`, `network`, `address`, `wif`, `script_hash`, `created_at`)
        VALUES
          (:uid, :coin, :network, :address, :wif, :script_hash, :created_at)
    ';

    $statement = $this->getDb()->prepare($wallet_query);
    $statement->bindParam('uid', $wallet->uid);
    $statement->bindParam('coin', $wallet->coin);
    $statement->bindParam('network', $wallet->network);
    $statement->bindParam('address', $wallet->address);
    $statement->bindParam('wif', $wallet->wif);
    $statement->bindParam('script_hash', $wallet->script_hash);
    $statement->bindParam('created_at', $wallet->created_at);
    $statement->execute();

    $walletId = (int) $this->database->lastInsertId();

    return $walletId;
  }
}