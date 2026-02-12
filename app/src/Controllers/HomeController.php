<?php

namespace App\Controllers;

class HomeController
{
    public function home($vars = [])
    {
        // normally we don't want to echo from a controller method directly
        // but rather load a view template
        echo "Welcome home!";
    }
}
