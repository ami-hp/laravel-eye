<?php

return [

    //name of the table which visit records should save in
    'table_name' =>  'ami_eye_visits',


    /*
    |--------------------------------------------------------------------------
    | Cookies
    |--------------------------------------------------------------------------
    |
    | This package binds visitors to views using a cookie. If you want to
    | give this cookie a custom name, you can specify that here.
    |
    */
    'cache' => [
        'key' => 'ami.laravel-eye.records',
        'max_count' => 1000*1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookies
    |--------------------------------------------------------------------------
    |
    | This package binds visitors to views using a cookie. If you want to
    | give this cookie a custom name, you can specify that here.
    |
    */
    'cookie' =>[
        'key' => 'eye__visitor',
        'expire_time' => 2628000, // aka 5 years
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following driver to use.
    | You can switch to a different driver at runtime.
    |
    */
    'default_driver' => 'jenssegers',


    //TODO Crawler
];
