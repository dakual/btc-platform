<?php
namespace App\Repository;

use App\Entity\WalletEntity;

class WalletRepository extends BaseRepository
{
  public function getWallet(string $currency, string $network, string $userId): Array
  {
    $query = '
        SELECT md5(w.id) AS wid, w.address, w.wif, w.created_at 
        FROM `wallets` w 
        WHERE w.uid = :uid AND w.currency = :currency AND w.network = :network 
        ORDER BY w.created_at DESC
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $userId);
    $statement->bindParam('currency', $currency);
    $statement->bindParam('network', $network);
    $statement->execute();

    $wallets = (array) $statement->fetchAll(\PDO::FETCH_CLASS, WalletEntity::class) ?: [];
    if (! $wallets) {
        throw new \Exception('Wallet not found.', 404);
    }

    return [
      "uid"      => $userId,
      "currency" => $currency,
      "network"  => $network,
      "wallets"  => $wallets
    ];
  }

  public function createWallet(WalletEntity $wallet): int
  {
    $wallet_query = '
        INSERT INTO `wallets`
          (`uid`, `currency`, `network`, `address`, `wif`, `created_at`)
        VALUES
          (:uid, :currency, :network, :address, :wif, :created_at)
    ';

    $statement = $this->getDb()->prepare($wallet_query);
    $statement->bindParam('uid', $wallet->uid);
    $statement->bindParam('currency', $wallet->currency);
    $statement->bindParam('network', $wallet->network);
    $statement->bindParam('address', $wallet->address);
    $statement->bindParam('wif', $wallet->wif);
    $statement->bindParam('created_at', $wallet->created_at);
    $statement->execute();

    $walletId = (int) $this->database->lastInsertId();

    return $walletId;
  }

  public function saveWithdraw(array $tx): void
  {
    $query = '
        INSERT INTO `withdraws` 
          (`tid`, `uid`, `currency`, `network`, `address`, `fee`, `amount`, `hex`, `created_at`) 
        VALUES 
          (:tid, :uid, :currency, :network, :address, :fee, :amount, :hex, :created_at);
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('tid', $tx['transaction']['tx_id']);
    $statement->bindParam('uid', $tx['uid']);
    $statement->bindParam('currency', $tx['currency']);
    $statement->bindParam('network', $tx['network']);
    $statement->bindParam('address', $tx['transaction']['address']);
    $statement->bindParam('fee', $tx['transaction']['fee']);
    $statement->bindParam('amount', $tx['transaction']['amount']);
    $statement->bindParam('hex', $tx['transaction']['tx_hex']);
    $statement->bindParam('created_at', $tx['transaction']['created']);
    $statement->execute();
  }

  public function getWithdraw(string $uid, string $tid): array
  {
    $query = '
        SELECT * FROM `withdraws` WHERE tid = :tid AND `uid` = :uid
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

  public function getWithdrawals(string $currency, string $network, string $userId): Array
  {
    $query = '
        SELECT * FROM 
          `withdraws` 
        WHERE 
          uid = :uid AND currency = :currency AND network = :network 
        ORDER BY `created_at` DESC
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $userId);
    $statement->bindParam('currency', $currency);
    $statement->bindParam('network', $network);
    $statement->execute();

    $withdrawals = (array) $statement->fetchAll(\PDO::FETCH_CLASS) ?: [];
    if (! $withdrawals) {
      throw new \Exception('Wallet not found.', 404);
    }

    return [
      "uid"      => $userId,
      "currency" => $currency,
      "network"  => $network,
      "withdrawals" => $withdrawals
    ];
  }

  public function saveTransaction(array $tx): void
  {
    $query = '
        INSERT INTO `transactions` 
          (`tid`, `uid`, `currency`, `network`) 
        VALUES 
          (:tid, :uid, :currency, :network);
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('tid', $tx["tid"]);
    $statement->bindParam('uid', $tx["uid"]);
    $statement->bindParam('currency', $tx["currency"]);
    $statement->bindParam('network', $tx["network"]);
    $statement->execute();
  }

  // public function updateWithdraw(string $uid, string $tid, string $status): void
  // {
  //   $now   = date('Y-m-d\TH:i:s.uP', time());
  //   $query = '
  //       UPDATE `withdraws` 
  //       SET `status` = :status, `updated_at` = :updated_at 
  //       WHERE tid = :tid AND `uid` = :uid
  //   ';

  //   $statement = $this->getDb()->prepare($query);
  //   $statement->bindParam('status', $status);
  //   $statement->bindParam('updated_at', $now);
  //   $statement->bindParam('tid', $tid);
  //   $statement->bindParam('uid', $uid);
  //   $statement->execute();
  // }
}