<?php

return [

    /*
     * name of the table which visit records should save in
     */
    'table_name' =>  'eye_visits',


    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | This package can store visits in cache by a method "viaCache"
    | and you can specify that if the count of visits in cache
    | reaches to max_count all visits will be inserted to database
    | this will help to increase performance for a medium rate of visits
    | and prevent reaching the memory limitations
    |
    */

    'cache' => [
        'key'       => 'eye__records',
        'max_count' => 1000000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cookies
    |--------------------------------------------------------------------------
    |
    | This package binds visitors to views using a cookie. If you want to
    | give this cookie a custom name, you can specify that here.
    | when the user's cookie expires a new cookie will be set and
    | the user is declared as a new one.
    |
    */

    'cookie' =>[
        'key'         => 'eye__visitor',
        'expire_time' => 2628000, //in minutes aka 5 years
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Parser
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following parser to use.
    | You can switch to a different parser at runtime.
    | you can choose between:
    | ► jenssegers
    | ► UAParser
    |
    */

    'default_parser' => 'jenssegers',

    /*
     | if you change ignore_bots to False
     | the package will record the entrance of crawlers
     | the crawlers are defined in a package called: jaybizzle\crawler-detect
     */
    'ignore_bots' => true,

    /*
     | if Queue mode the package will use Jobs
     | to insert Visits to Database
     */
    'queue' => true,
];
