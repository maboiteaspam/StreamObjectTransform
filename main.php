<?php
require 'vendor/autoload.php';

// to rewrite..


use \C\Stream\StreamFlow;
use \C\Stream\StreamObjectTransform;


StreamObjectTransform::through()
    ->pipe( StreamFlow::duplex(2)
            ->pipe(function($chunk){
                var_dump("duplexed $chunk");
            })
    )
    ->pipe(function($chunk){
        var_dump("not duplexed $chunk");
    })
    ->write('some');

