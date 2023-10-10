<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** {CLASSNAME}
 * ==================           ==================
 * ===============================================
 */

namespace Bolt\controllers;

use Bolt\Bolt\Controller;

class {CLASSNAME} extends Controller
{
    public function welcome()
    {
        $data = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("{VIEWPATH}", $data);
    }

    public function onConstruct(): void
    {   
    }

    public function users()
    {
        $user = new Users();

        $data = [
            "username" => "",
            "email" => "",
            "password" => ""
        ];

        $u = $user->findOneBy(["acl" => "guest"]);
        
        dd($u);
    }
}