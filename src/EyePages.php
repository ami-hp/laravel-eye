<?php


namespace Ami\Eye;


use App\Models\Brand;
use App\Models\Consult;
use App\Models\ConsultCategory;
use App\Models\Landing;
use App\Models\News;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductCat;
use App\Models\Research;
use App\Models\ResearchCategory;
use App\Models\SiteInformation;

class EyePages
{
    protected static $cache_name = 'view';
    public
        $table_total   = "eye_total_views",
        $table_details = "eye_detailed_views";




    /**
     *! Define All Cache Names and its Types
     ** cache_name => "page_type"
     *
     *? ==== Where To Use CacheName ==== **
     ** Eye::set_cache_views( cache_name , id = 0);
     *
     *? ==== Where To Use PageType ==== **
     ** in Database
     *
     * @return array
     */
    protected function getTypes()
    {
        return [
            'cache_name_1'   => "type_in_database_1",
            'cache_name_2'   => "type_in_database_2",
            'cache_name_3'   => "type_in_database_3",
            'cache_name_4'   => "type_in_database_4",
        ];
    }

    protected function typeGroups(){
        return [
            "cache_group1" => [
                'cache_name_1'   => "type_in_database_1",
                'cache_name_2'   => "type_in_database_2",
            ],
            "cache_group2" => [
                'cache_name_3'   => "type_in_database_3",
                'cache_name_4'   => "type_in_database_4",
            ],
        ];
    }


}
