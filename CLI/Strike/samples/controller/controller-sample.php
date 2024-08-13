<?php

declare(strict_types=1);

/**
 * ===============================================
 * ==================           ==================
 * ****** {CLASSNAME}
 * ==================           ==================
 * ===============================================
 */

namespace PhpStrike\app\controllers;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;

use celionatti\Bolt\Controller;

class {CLASSNAME} extends Controller
{
    public $currentUser = null;

    public function onConstruct(): void
    {
        $this->currentUser = user();
        // To add middleware, if middleware is for all the controller page, dont all the array. ['users'].
        $this->registerMiddleware(new AuthMiddleware(['users'])); 

        // if (!hasAccess([], 'all', ['user', 'guest'])) {
        //     redirect("/", 401);
        // }   
    }

    public function index($name, Request $request, Response $reponse)
    {
        $rules = [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|unique:users.email',
            'password' => 'required|string|min:6|confirmed',
        ];

        $users = new User();

        $attributes = [
            'user_id' => bolt_uuid(),
            'name' => 'celio tinny',
            'email' => 'celiotinny@example.com',
            'password' => 'passwordtinny',
            'password_confirm' => 'passwordtinny',
            'remember_token' => stringToken()
        ];

        /** For Creating a new Data. */
        if($request->validate($rules, $attributes)) {
            if($users->create($attributes)) {
                dump("User Created Successfully!");
            }
        } else {
            dump($request->getErrors());
        }

        /** For Updating data */
        if (!$users->update("bv_0faafa7e3f0fb6dd972e82576708f569", $attributes)) {
            var_dump("Update Failed!");
        } else {
            echo "User updated successfully!";
        }

        $user = $users->findUser("bv_0fac6ee9a715a02d3f03339c3b355875");
        $post = $posts->first();
        // $user = $users->findByEmail("test@example.com");
        // $user = $users->get();

        // dump($user->posts()->get());
        // dump($post->user()->get());

        $view = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("welcome", $view);
    }
}