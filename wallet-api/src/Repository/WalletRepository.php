<?php
namespace App\Repository;

use App\Entity\WalletEntity;

class WalletRepository extends BaseRepository
{
  public function getWallet(string $coin, string $network, string $userId): Array
  {
    $query = '
        SELECT md5(w.id) wid, w.uid, w.coin, w.network, w.address, w.wif, w.created_at FROM `wallets` w 
        WHERE w.uid = :uid AND w.coin = :coin AND w.network = :network
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

    return $wallets;
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

  public function saveTx(array $tx): int
  {
    $query = '
        INSERT INTO `transactions` 
          (`uid`, `coin`, `network`, `address`, `input_count`, `output_count`, `fee`, `fee_rate`, `unspent`, `amount`, `residue`, `tx_id`, `tx_hex`, `created_at`, `status`) 
        VALUES 
          (:uid, :coin, :network, :address, :input_count, :output_count, :fee, :fee_rate, :unspent, :amount, :residue, :tx_id, :tx_hex, :created_at, :status);
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $tx["uid"]);
    $statement->bindParam('coin', $tx["coin"]);
    $statement->bindParam('network', $tx["network"]);
    $statement->bindParam('address', $tx["address"]);
    $statement->bindParam('input_count', $tx["input_count"]);
    $statement->bindParam('output_count', $tx["output_count"]);
    $statement->bindParam('fee', $tx["fee"]);
    $statement->bindParam('fee_rate', $tx["fee_rate"]);
    $statement->bindParam('unspent', $tx["unspent"]);
    $statement->bindParam('amount', $tx["amount"]);
    $statement->bindParam('residue', $tx["residue"]);
    $statement->bindParam('tx_id', $tx["tx_id"]);
    $statement->bindParam('tx_hex', $tx["tx_hex"]);
    $statement->bindParam('created_at', $tx["created_at"]);
    $statement->bindParam('status', $tx["status"]);
    $statement->execute();

    return (int)$this->database->lastInsertId();
  }

  public function getTx(string $uid, string $txid): array
  {
    $query = '
        SELECT * FROM `transactions` WHERE md5(id) = :id AND `uid` = :uid
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('id', $txid);
    $statement->bindParam('uid', $uid);
    $statement->execute();

    $tx = $statement->fetch(\PDO::FETCH_ASSOC);
    if (! $tx) {
      throw new \Exception('Transaction not found.', 400);
    }

    return $tx;
  }

  public function updateTx(string $uid, string $txid, string $status): void
  {
    $now   = date('Y-m-d\TH:i:s.uP', time());
    $query = '
        UPDATE `transactions` 
        SET `status` = :status, `updated_at` = :updated_at 
        WHERE md5(id) = :id AND `uid` = :uid
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('status', $status);
    $statement->bindParam('updated_at', $now);
    $statement->bindParam('id', $txid);
    $statement->bindParam('uid', $uid);
    $statement->execute();
  }

  public function getAllTx(string $coin, string $network, string $userId): Array
  {
    $query = '
        SELECT md5(id) tid, `uid`, `coin`, `network`, `address`, `input_count`, `output_count`, `fee`, `fee_rate`, `unspent`, `amount`, `residue`, `tx_id`, `tx_hex`, `created_at`, `updated_at`, `status` FROM 
          `transactions` t 
        WHERE 
          t.uid = :uid AND t.coin = :coin AND t.network = :network
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

    return $allTx;
  }
}