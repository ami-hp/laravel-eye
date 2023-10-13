<?php

namespace Ami\Eye\Services;

use Ami\Eye\Contracts\DataManagementInterface;
use Ami\Eye\Jobs\InsertVisit;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Queue;

class Databaser implements DataManagementInterface
{

    protected $eye;

    protected $once = false;

    public $period = null;


    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
    }

    protected function eye(): EyeService
    {
        return $this->eye;
    }

    public function period(Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function once(): self
    {
        $this->once = true;

        return $this;
    }

    public function query(): Builder
    {
        return Visit::query();
    }

    public function get()
    {

    }

    /**
     * @throws Exception
     */
    public function record(?Model $visitable = null, ?Model $visitor = null, bool $once = false)
    {
        if ($visitor !== null) $this->eye()->setVisitor($visitor);

        if ($visitable !== null) $this->eye()->setVisitable($visitable);

        if (!$this->shouldRecord($once)) return false;

        if($once || $this->once) $once = true;

        $visit = $this->eye()->getCurrentVisit();

        if($this->eye()->config['eye']['queue']){
            Queue::push(new InsertVisit($visit, $visitable, $visitor, $once));
        } else {
            self::insert($visit, $visitable, $visitor, $once);
        }


        return $visit;
    }

    /**
     * @throws Exception
     */
    protected function shouldRecord(bool $once = false): bool
    {
        // If ignore bots is true and the current visitor is a bot, return false
        if ($this->eye()->config['eye']['ignore_bots'] && $this->eye()->detector->isCrawler()) {
            return false;
        }

        return true;
    }

    /**
     * Deprecated
     * @return bool
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

    public static function insert(Visit $visit , $visitable = null , $visitor = null , $once = false)
    {
        $visitArray = $visit->toArray();

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
                $visit->save();
            }
        }

        return $visit;
    }
}