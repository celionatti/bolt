<?php

declare(strict_types=1);

namespace Bolt\controllers;

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;

class UserController
{
    public function index()
    {
        echo 'Welcome to the User page!';
    }
}