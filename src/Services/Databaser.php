<?php

namespace Ami\Eye\Services;

use Ami\Eye\Contracts\DataManagementInterface;
use Ami\Eye\Jobs\InsertVisit;
use Ami\Eye\Models\Visit;
use Ami\Eye\Support\Period;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;

class Databaser implements DataManagementInterface
{

    protected $eye;

    protected $query;

    protected $once = false;

    private $unique = '*';

    private $visitor = false;

    private $visitable = true;

    public function __construct(EyeService $eye)
    {
        $this->eye = $eye;
        $this->query = $this->queryInit();
//        $this->query();
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

        if($this->visitable){
            if($visitable = $this->eye()->getVisitableModel())
                $this->query = $query->whereVisitable($visitable);
            else
                $this->query = $query->whereUrl($this->eye()->url());
        }

        if($this->visitor)
            $this->query = $query->whereVisitor($this->eye()->getVisitorModel());


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
     * Only Works on Count in database
     * @param string $column
     * @return $this
     */
    public function unique(string $column = 'unique_id'): self
    {

        $this->query = $this->query->distinct();

        $this->unique = $column;

        return $this;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function collection(?string $name = null) : self
    {

        $this->query = $this->query->whereCollection($name);
        $this->eye()->setCollection($name);

        return $this;
    }

    /**
     * @param Model|null $user
     * @param bool       $whereMode
     * @return self
     */
    public function visitor(?Model $user = null , bool $whereMode = true): self
    {
        $this->eye()->setVisitor($user);

        $this->visitor = $whereMode;

        return $this;
    }

    /**
     * @param Model|bool|null $post
     * @return self
     */
    public function visitable($post = null): self
    {
        if($post === false) //Disables Where
            $this->visitable = false;
        elseif($post instanceof Model || $post === null) // Enables Where Post for Model or Url for Null
            $this->eye()->setVisitable($post);

        return $this;
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        $this->query();

        return $this->query->get()->collect();
    }

    /**
     * @return int
     */
    public function count() : int
    {
        $this->query();

        return $this->query->count($this->unique);
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
     * @return void
     */
    public function truncate(): void
    {
        $this->queryInit()->truncate();
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $this->query();

        return $this->query->delete();
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