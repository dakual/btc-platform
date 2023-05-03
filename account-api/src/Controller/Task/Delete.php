<?php
namespace App\Controller\Task;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\TaskRepository;
use App\Entity\TaskEntity;

class Delete extends BaseController
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

    $task = $this->repository->getTask($taskId, $userId);
    $this->repository->delete($taskId, $userId);
    $data = array(
      'message' => 'Task successfully deleted!',
      'task'    => $task
    );

    return $this->jsonResponse($response, 'success', $data, 200);
  }
}