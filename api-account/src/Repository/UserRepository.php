<?php
namespace App\Repository;

use App\Entity\UserEntity;

class UserRepository extends BaseRepository
{
  public function loginUser(string $email, string $password): UserEntity
  {
    $query = 'SELECT * FROM `users` WHERE `username` = :username ORDER BY `id`';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $email);
    $statement->execute();

    $user = $statement->fetchObject(UserEntity::class);
    if (! $user) {
        throw new \App\Exception\Auth(
            'Login failed: Username or password incorrect!', 400
        );
    }

    return $user;
  }

  public function createUser(UserEntity $user): UserEntity
  {
    $query     = 'INSERT INTO `users` (`name`, `username`, `password`) VALUES (:name, :username, :password)';
    $statement = $this->database->prepare($query);
    $name      = $user->name;
    $username  = $user->username;
    $password  = $user->password;

    $statement->bindParam('name', $name);
    $statement->bindParam('username', $username);
    $statement->bindParam('password', $password);
    $statement->execute();

    return $this->getUser($username);
  }

  public function getUser(string $username): UserEntity
  {
    $query     = 'SELECT `id`, `name`, `username` FROM `users` WHERE `username` = :username';
    $statement = $this->database->prepare($query);
    $statement->bindParam('username', $username);
    $statement->execute();
    $user = $statement->fetchObject(UserEntity::class);
    if (! $user) {
        throw new \App\Exception\Auth('User not found.', 404);
    }

    return $user;
  }

  public function checkUserByUsername(string $username): void
  {
      $query     = 'SELECT * FROM `users` WHERE `username` = :username';
      $statement = $this->database->prepare($query);
      $statement->bindParam('username', $username);
      $statement->execute();
      $user = $statement->fetchObject();
      if ($user) {
          throw new \App\Exception\Auth('Username already exists.', 400);
      }
  }
}