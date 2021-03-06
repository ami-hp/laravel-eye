<?php

return [

    /**
     *! Define All Cache Names and its Types
     ** cache_name => "page_type"
     *
     *? ==== Where To Use CacheName ==== **
     ** Eye::setAndGetViews( cache_name , id = 0);
     *
     *? ==== Where To Use PageType ==== **
     ** in Database
     *
     */
    'cache_types' => [
        'cache_name_1'   => "type_1",
        'cache_name_2'   => "type_2",
        'cache_name_3'   => "type_3",
        'cache_name_4'   => "type_4",
    ],

    /**
     * Takes place in Total Table
     */
    'type_groups' => [

        "cache_group1" => [
            'cache_name_1',
            'cache_name_2',
        ],

        "cache_group2" => [
            'cache_name_3',
            'cache_name_4',
        ],

    ],


    /**
     * Name of tables
     */
    'tables' => [
        'total'   => "eye_total_views",
        'details' => "eye_detailed_views",
    ],
];
