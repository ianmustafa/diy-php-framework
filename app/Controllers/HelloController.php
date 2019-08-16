<?php

namespace App\Controllers;

class HelloController {
    public function hello($name = 'world')
    {
        echo "Hello {$name}!";
    }

    public function fromPost()
    {
        echo 'Hello world from POST!';
    }
}