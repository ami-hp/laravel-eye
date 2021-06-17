<?php

return [



    'tables' => [
        'total'   => "eye_total_views",
        'details' => "eye_detailed_views",
    ],


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
        'cache_name_1'   => "type_in_database_1",
        'cache_name_2'   => "type_in_database_2",
        'cache_name_3'   => "type_in_database_3",
        'cache_name_4'   => "type_in_database_4",
    ],

    'type_groups' => [

        "cache_group1" => [
            'cache_name_1'   => "type_in_database_1",
            'cache_name_2'   => "type_in_database_2",
        ],

        "cache_group2" => [
            'cache_name_3'   => "type_in_database_3",
            'cache_name_4'   => "type_in_database_4",
        ],

    ],

];
