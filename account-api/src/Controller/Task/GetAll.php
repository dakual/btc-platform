<?php
namespace App\Controller\Task;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Controller\BaseController;
use App\Repository\TaskRepository;
use App\Entity\TaskEntity;

class GetAll extends BaseController
{
  private TaskRepository $repository;

  public function __construct()
  {
    $this->repository = new TaskRepository();
  }

  public function __invoke(Request $request, Response $response): Response
  {    
    $userId = $this->getUserId($request);
    $params = $request->getQueryParams();

    $params['perPage'] = empty($params['perPage']) ? 5 : $params['perPage'];
    $params['page']    = empty($params['page']) ? 1 : $params['page'];
    $params['title']   = empty($params['title']) ? '' : $params['title'];
    $params['status']  = empty($params['status']) ? '' : $params['status'];

    $tasks = $this->repository->getAll($userId, $params);

    return $this->jsonResponse($response, 'success', $tasks, 200);
  }
}