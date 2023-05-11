<?php
namespace App\Repository;

use App\Entity\TaskEntity;

class TaskRepository extends BaseRepository
{

  public function getAll(string $userId, array $params): array
  {
    $query = "
        SELECT * FROM `tasks`
          WHERE `uid` = :uid
            AND `title` LIKE CONCAT('%', :title, '%')
            AND `status` LIKE CONCAT('%', :status, '%')
        ORDER BY `id`
    ";

    $bindParams = [
      'uid'    => $userId,
      'title'  => empty($params['title']) ? '' : $params['title'],
      'status' => empty($params['status']) ? '' : $params['status']
    ];

    $statement = $this->getDb()->prepare($query);
    $statement->execute($bindParams);
    $total = $statement->rowCount();

    $page    = (int) $params['page'];
    $perPage = (int) $params['perPage'];
    $offset  = ($page - 1) * $perPage;
    $query  .= " LIMIT ${perPage} OFFSET ${offset}";

    $statement = $this->getDb()->prepare($query);
    $statement->execute($bindParams);

    $result = array();
    $result['pagination'] = array(
      'totalRows'   => $total,
      'totalPages'  => ceil($total / $perPage),
      'currentPage' => $page,
      'perPage'     => $perPage,
    );
    $result['tasks'] = (array) $statement->fetchAll(\PDO::FETCH_CLASS, TaskEntity::class) ?: [];

    return $result;
  }

  public function create(TaskEntity $task): TaskEntity
  {
    $query = '
        INSERT INTO `tasks`
          (`uid`, `title`, `status`, `createdAt`, `updatedAt`)
        VALUES
          (:uid, :title, :status, :createdAt, :updatedAt)
    ';

    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('uid', $task->uid);
    $statement->bindParam('title', $task->title);
    $statement->bindParam('status', $task->status);
    $statement->bindParam('createdAt', $task->createdAt);
    $statement->bindParam('updatedAt', $task->updatedAt);
    $statement->execute();

    $taskId = (int) $this->database->lastInsertId();

    return $this->getTask($taskId, $task->uid);
  }

  public function getTask(int $taskId, string $userId): TaskEntity
  {
    $query = '
        SELECT * FROM `tasks` WHERE `id` = :id AND `uid` = :uid
    ';
    $statement = $this->getDb()->prepare($query);
    $statement->bindParam('id', $taskId);
    $statement->bindParam('uid', $userId);
    $statement->execute();

    $task = $statement->fetchObject(TaskEntity::class);
    if (! $task) {
        throw new \App\Exception\Auth('Task not found.', 404);
    }

    return $task;
  }

  public function update(TaskEntity $task): TaskEntity
  {
      $query = '
          UPDATE `tasks`
          SET `title` = :title, `status` = :status, `updatedAt` = :updated 
          WHERE `id` = :id AND `uid` = :uid
      ';
      $statement = $this->getDb()->prepare($query);
      $statement->bindParam('title', $task->title);
      $statement->bindParam('status', $task->status);
      $statement->bindParam('updated', $task->updatedAt);
      $statement->bindParam('id', $task->id);
      $statement->bindParam('uid', $task->uid);
      $statement->execute();

      return $this->getTask($task->id, $task->uid);
  }

  public function delete(int $taskId, string $userId): void
  {
      $query = '
          DELETE FROM `tasks` WHERE `id` = :id AND `uid` = :uid
      ';
      $statement = $this->getDb()->prepare($query);
      $statement->bindParam('id', $taskId);
      $statement->bindParam('uid', $userId);
      $statement->execute();
  }
}