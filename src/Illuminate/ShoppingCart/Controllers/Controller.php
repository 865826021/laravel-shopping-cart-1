<?php

namespace PhpSoft\Illuminate\ShoppingCart\Controllers;

use App\Http\Controllers\Controller as AppController;

class Controller extends AppController
{
    /**
     * Instantiate a new Controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $app = app();
        $app['view']->addLocation(__DIR__.'/../resources/views');
    }
}
