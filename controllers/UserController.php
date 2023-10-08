<?php

declare(strict_types=1);

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

class UserController extends Controller
{
    public function index()
    {
        echo 'Welcome to the User page!';
    }

    public function dashboard()
    {
        echo 'Welcome to the User Dashboard Page!';
    }

    public function create()
    {
        echo 'Welcome to the User Create Page!';
    }

    public function admin($id)
    {
        dd($id);
        echo 'Welcome to the User Admin Page!';
    }
}