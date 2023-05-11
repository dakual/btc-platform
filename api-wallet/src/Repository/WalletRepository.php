<?php
namespace App\Repository;

use App\Entity\WalletEntity;

class WalletRepository extends BaseRepository
{
  public function getWallet(string $coin, string $network, string $userId): Array
  {
    $query = '
        SELECT md5(w.id) AS wid, w.address, w.wif, w.created_at 
        FROM `wallets` w 
        WHERE w.uid = :uid AND w.coin = :coin AND w.network = :network 
        ORDER BY w.created_at DESC
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $userId);
    $statement->bindParam('coin', $coin);
    $statement->bindParam('network', $network);
    $statement->execute();

    $wallets = (array) $statement->fetchAll(\PDO::FETCH_CLASS, WalletEntity::class) ?: [];
    if (! $wallets) {
        throw new \Exception('Wallet not found.', 404);
    }

    return [
      "uid"     => $userId,
      "coin"    => $coin,
      "network" => $network,
      "wallets" => $wallets
    ];
  }

  public function createWallet(WalletEntity $wallet): int
  {
    $wallet_query = '
        INSERT INTO `wallets`
          (`uid`, `coin`, `network`, `address`, `wif`, `created_at`)
        VALUES
          (:uid, :coin, :network, :address, :wif, :created_at)
    ';

    $statement = $this->getDb()->prepare($wallet_query);
    $statement->bindParam('uid', $wallet->uid);
    $statement->bindParam('coin', $wallet->coin);
    $statement->bindParam('network', $wallet->network);
    $statement->bindParam('address', $wallet->address);
    $statement->bindParam('wif', $wallet->wif);
    $statement->bindParam('created_at', $wallet->created_at);
    $statement->execute();

    $walletId = (int) $this->database->lastInsertId();

    return $walletId;
  }

  public function saveTx(array $tx): void
  {
    $query = '
        INSERT INTO `transactions` 
          (`tid`, `uid`, `coin`, `network`, `address`, `fee`, `amount`, `hex`, `created_at`, `status`) 
        VALUES 
          (:tid, :uid, :coin, :network, :address, :fee, :amount, :hex, :created_at, :status);
    ';
    $rs = $tx["transaction"];

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('tid', $rs["tx_id"]);
    $statement->bindParam('uid', $tx["uid"]);
    $statement->bindParam('coin', $tx["coin"]);
    $statement->bindParam('network', $tx["network"]);
    $statement->bindParam('address', $rs["address"]);
    $statement->bindParam('fee', $rs["fee"]);
    $statement->bindParam('amount', $rs["amount"]);
    $statement->bindParam('hex', $rs["tx_hex"]);
    $statement->bindParam('created_at', $rs["created_at"]);
    $statement->bindParam('status', $rs["status"]);
    $statement->execute();
  }

  public function getTx(string $uid, string $tid): array
  {
    $query = '
        SELECT * FROM `transactions` WHERE tid = :tid AND `uid` = :uid
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('tid', $tid);
    $statement->bindParam('uid', $uid);
    $statement->execute();

    $tx = $statement->fetch(\PDO::FETCH_ASSOC);
    if (! $tx) {
      throw new \Exception('Transaction not found.', 400);
    }

    return $tx;
  }

  public function updateTx(string $uid, string $tid, string $status): void
  {
    $now   = date('Y-m-d\TH:i:s.uP', time());
    $query = '
        UPDATE `transactions` 
        SET `status` = :status, `updated_at` = :updated_at 
        WHERE tid = :tid AND `uid` = :uid
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('status', $status);
    $statement->bindParam('updated_at', $now);
    $statement->bindParam('tid', $tid);
    $statement->bindParam('uid', $uid);
    $statement->execute();
  }

  public function getAllTx(string $coin, string $network, string $userId): Array
  {
    $query = '
        SELECT `tid`, `address`, `amount`, `fee`, `created_at`, `updated_at`, `status` FROM 
          `transactions` t 
        WHERE 
          t.uid = :uid AND t.coin = :coin AND t.network = :network 
        ORDER BY `created_at` DESC
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $userId);
    $statement->bindParam('coin', $coin);
    $statement->bindParam('network', $network);
    $statement->execute();

    $allTx = (array) $statement->fetchAll(\PDO::FETCH_CLASS) ?: [];
    if (! $allTx) {
      throw new \Exception('Wallet not found.', 404);
    }

    return [
      "uid"     => $userId,
      "coin"    => $coin,
      "network" => $network,
      "transactions" => $allTx
    ];
  }

}