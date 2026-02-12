<?php

namespace App\Controllers;

class HelloController
{
    public function greet($vars = [])
    {
        // normally we don't want to echo from a controller method directly
        // but rather load a view template
        $name = $vars['name'] ?? 'World';
        echo "Hi, {$name}!";
    }
}
