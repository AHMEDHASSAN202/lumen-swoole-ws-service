<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function disconnectDatabase()
    {
        app('db')->disconnect(env('DB_CONNECTION', 'mysql'));
    }
}
