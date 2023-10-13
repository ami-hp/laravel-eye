<?php

namespace Ami\Eye\Services;

use Ami\Eye\Contracts\DataManagementInterface;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

        $visit = $this->eye()->getCurrentVisit();
        $visitArray = $visit->toArray();

        if ($once || $this->once) {

            $selectArray =
                $visitable !== null
                ? ['visitable_id', 'visitable_type', 'unique_chin']
                : ['url', 'unique_id'];

            $selectColumns = array_flip($selectArray);

            $checkArray = array_intersect_key($visitArray, $selectColumns);

            $insertArray = array_diff_key($visitArray, $selectColumns);

            $visit = Visit::firstOrCreate(
                array_filter($checkArray),
                array_filter($insertArray)
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

    /**
     * @throws Exception
     */
    protected function shouldRecord(bool $once = false): bool
    {
        // If ignore bots is true and the current visitor is a bot, return false
        if ($this->eye()->config['eye']['ignore_bots'] && $this->eye()->detector->isCrawler()) {
            return false;
        }

        if($once === true || $this->once){
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

            if($query->exists()) return false;
        }

        return true;
    }
}