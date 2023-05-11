<?php
namespace App\Controller\Task;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\TaskRepository;
use App\Entity\TaskEntity;

class Update extends BaseController
{
  private TaskRepository $repository;

  public function __construct()
  {
    $this->repository = new TaskRepository();
  }

  public function __invoke(Request $request, Response $response, array $args): Response
  {
    $taskId = (int) $args['id'];
    $userId = $this->getUserId($request);
    $data   = (array) $request->getParsedBody();
    $data   = json_decode(json_encode($data), false);
    if(! isset($data->title)) {
      throw new \App\Exception\Auth('The field "title" is required.', 400);
    }
    if(! isset($data->status)) {
      throw new \App\Exception\Auth('The field "status" is required.', 400);
    }

    $now  = date('Y-m-d\TH:i:s.uP', time());
    $task = new TaskEntity();
    $task->id        = $taskId;
    $task->uid       = $userId;
    $task->title     = $data->title;
    $task->status    = $data->status;
    $task->updatedAt = $now;

    $task = $this->repository->update($task);
    $data = array(
      'task' => $task
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}