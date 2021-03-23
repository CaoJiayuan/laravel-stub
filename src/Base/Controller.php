<?php namespace Nerio\LaravelStub\Base;

use CaoJiayuan\LaravelApi\Http\Request\RequestHelper;
use CaoJiayuan\LaravelApi\Http\Response\ResponseHelper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use RequestHelper, ResponseHelper;
}
