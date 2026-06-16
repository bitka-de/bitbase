<?php

namespace App\Http\Controllers;

use App\Services\RobotsService;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(RobotsService $robotsService): Response
    {
        return response($robotsService->content(), 200, ['Content-Type' => 'text/plain']);
    }
}
