<?php

namespace Ami\Eye\Contracts;

use Ami\Eye\Support\Period;
use Illuminate\Database\Eloquent\Model;

interface DataManagementInterface
{

    /**
     * Where Methods
     * -----------
     * - period     : whereInBetween
     * - unique     : where the value in the column is unique
     * - collection : whereCollection
     *
     */

    /**
     * Where between two dates
     * @param Period $period
     */
    public function period(Period $period);

    /**
     * Where the value in the column is unique
     * @param string $column
     */
    public function unique(string $column = 'unique_id');

    /**
     * WhereCollection
     * @param string|null $name
     */
    public function collection(?string $name = null);




    /**
     * Fetch Methods
     * -----------
     * - get       : get collection of visits
     * - count     : count the collection
     *
     */

    /**
     * get collection of visits
     */
    public function get();

    /**
     * count the collection of visits
     * @return int
     */
    public function count(): int;





    /**
     * Insert Methods
     * -----------
     * - once   : only if the user didn't visit
     * - record : insert
     *
     */

    /**
     * Adds a condition to record:
     * only if the user didn't visit
     */
    public function once();

    /**
     * insert the visit
     * @param bool       $once
     * @param Model|null $visitable
     * @param Model|null $visitor
     */
    public function record(bool $once = false , ?Model $visitable = null , ?Model $visitor = null) ;

}