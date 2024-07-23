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

use celionatti\Bolt\Controller;

class {CLASSNAME} extends Controller
{
    /**
     * Index Page of the Resources.
     * GET Request
     * @return void
     */
    public function index()
    {
        $data = [
            'title' => 'Bolt Framework',
            'header' => 'Hello, User! Welcome to Bolt Framework',
            'text' => 'You are most welcome to our world.',
            'items' => ['Item 1', 'Item 2', 'Item 3'],
        ];

        $this->view->render("{VIEWPATH}", $data);
    }

    /**
     * Create Page of the Resources.
     * GET Request
     * @return void
     */
    public function create()
    {
        
    }

    /**
     * Store the Created data- Page of the Resources.
     * POST Request
     * @return void
     */
    public function store()
    {
        
    }

    /**
     * Show a single data- Page of the Resources.
     * GET Request
     * Usage: www.bolt.com/posts/2
     * @return void
     */
    public function show()
    {

    }

    /**
     * Show Editable data of a single data- Page of the Resources.
     * GET Request
     * Usage: www.bolt.com/posts/2/edit
     * @return void
     */
    public function edit()
    {

    }

    /**
     * Update Editable data of a single data- Page of the Resources.
     * PUT Request or PATCH
     * Usage: www.bolt.com/posts/2
     * @return void
     */
    public function update()
    {

    }
    
    /**
     * Delete a single data- Page of the Resources.
     * DELETE Request
     * Usage: www.bolt.com/posts/2/
     * @return void
     */
    public function destory()
    {

    }

    public function onConstruct(): void
    {   
    }
}