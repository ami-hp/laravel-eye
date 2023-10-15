<?php

namespace Ami\Eye\Services;

use Ami\Eye\Contracts\DataManagementInterface;
use Ami\Eye\Jobs\InsertVisit;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

class Databaser implements DataManagementInterface
{

    protected $eye;

    protected $query;

    protected $once = false;

    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
        $this->query = Visit::query();
        $this->query();
    }

    /**
     * @return Builder
     */
    public function queryInit(): Builder
    {
        return Visit::query();
    }

    /**
     * @return $this
     */
    public function query() : self
    {

        $query = $this->query;

        if($visitable = $this->eye()->getVisitableModel())
            $this->query = $query->whereVisitable($visitable);
        else
            $this->query = $query->whereUrl($this->eye()->url());


        return $this;
    }

    /**
     * @param Period $period
     * @return $this
     */
    public function period(Period $period): self
    {
        $this->query = $this->query->withInPeriod($period);

        return $this;
    }

    /**
     * @return $this
     */
    public function once(): self
    {
        $this->once = true;

        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function unique(string $column = 'unique_id'): self
    {

        $this->query = $this->query->distinct($column);

        return $this;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function collection(?string $name = null) : self
    {

        $this->query = $this->query->whereCollection($name);

        return $this;
    }

    /**
     * @return Builder[]|Collection
     */
    public function get()
    {
        return $this->query->get();
    }

    /**
     * @return int
     */
    public function count() : int
    {
        return $this->query->count();
    }


    /**
     * @throws Exception
     */
    public function record(bool $once = false , ?Model $visitable = null, ?Model $visitor = null)
    {
        if ($visitor !== null) $this->eye()->setVisitor($visitor);

        if ($visitable !== null) $this->eye()->setVisitable($visitable);

        if (!$this->shouldRecord()) return false;

        if($once || $this->once) $once = true;

        $visit = $this->eye()->getCurrentVisit();

        if($this->eye()->config['eye']['queue']){
            Queue::push(new InsertVisit($visit->toArray(), $visitable, $visitor, $once));
        } else {
            self::insert($visit->toArray(), $visitable, $visitor, $once);
        }


        return $visit;
    }

    /**
     * Protected Methods
     * ------------------
     */

    /**
     * @return EyeService
     */
    protected function eye(): EyeService
    {
        return $this->eye;
    }
    /**
     * @throws Exception
     */
    protected function shouldRecord(): bool
    {
        // If ignore bots is true and the current visitor is a bot, return false
        if ($this->eye()->config['eye']['ignore_bots'] && $this->eye()->detector->isCrawler()) {
            return false;
        }

        return true;
    }

    /**
     * Private Methods
     * ------------------
     */

    /**
     * Deprecated
     * @return Databaser
     * @throws Exception
     */
    private function shouldOnce() : self
    {
        $this->once = true;

        $visit = $this->eye()->getCurrentVisit();

        $query = Visit::query()->where('unique_id' , $visit->unique_id);

        if($visit->visitable_type !== null && $visit->visitable_id !== null)
        {
            $query =
                $query
                    ->where('visitable_type' , $visit->visitable_type)
                    ->where('visitable_id'   , $visit->visitable_id);
        }
        else{
            $query = $query->where('url' , $visit->url);
        }

        if($query->exists())
            $this->once = false;

        return $this;
    }

    /**
     * Static Methods
     * ----------------
     */

    /**
     * @param array $visitArray
     * @param null  $visitable
     * @param null  $visitor
     * @param bool  $once
     * @return mixed
     */
    public static function insert(array $visitArray , $visitable = null , $visitor = null , bool $once = false)
    {

        if ($once) {

            $selectArray =
                $visitable !== null
                    ? ['visitable_id', 'visitable_type', 'unique_id']
                    : ['url', 'unique_id'];

            $selectColumns = array_flip($selectArray);
            $checkArray    = array_intersect_key($visitArray, $selectColumns);
            $insertArray   = array_diff_key($visitArray, $selectColumns);

            $visit = Visit::firstOrCreate(
                array_filter($checkArray),
                $insertArray
            );

        } else {
            if ($visitor !== null && method_exists($visitor, 'visitLogs')) {
                $visit = $visitor->visitLogs()->create($visitArray);
            } else {
                $visit = Visit::create($visitArray);
            }
        }

        return $visit;
    }
}