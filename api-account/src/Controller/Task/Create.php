<?php
namespace App\Controller\Task;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\TaskRepository;
use App\Entity\TaskEntity;

class Create extends BaseController
{
  private TaskRepository $repository;

  public function __construct()
  {
    $this->repository = new TaskRepository();
  }

  public function __invoke(Request $request, Response $response): Response
  {
    $data = (array) $request->getParsedBody();
    $data = json_decode(json_encode($data), false);
    if(! isset($data->title)) {
      throw new \App\Exception\Auth('The field "title" is required.', 400);
    }

    $now  = date('Y-m-d\TH:i:s.uP', time());
    $task = new TaskEntity();
    $task->uid       = $this->getUserId($request);
    $task->title     = $data->title;
    $task->status    = $data->status;
    $task->createdAt = $now;
    $task->updatedAt = $now;

    $task = $this->repository->create($task);
    $data = array(
      'message' => 'Task successfully created!',
      'taskid'  => $task->id
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}