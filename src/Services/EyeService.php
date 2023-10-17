<?php


namespace Ami\Eye\Services;

use Ami\Eye\Contracts\DataManagementInterface;
use Ami\Eye\Support\Period;
use Ami\Eye\Traits\CrawlerDetection;
use Ami\Eye\Traits\DataPreparation;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class EyeService implements DataManagementInterface
{

    use DataPreparation;
    use CrawlerDetection;


    private $cache;

    private $database;

    public $storage = ['cache' , 'database' , 'redis'];

    /**
     * @throws Exception
     */
    public function __construct(?Model $visitable = null)
    {
        $this->initializeDataPrepTrait();
        $this->initializeCrawlerTrait();

        if($visitable){
            $this->setVisitable($visitable);
        }

        $this->cache    = $this->viaCache();
        $this->database = $this->viaDatabase();
    }

    /**
     * Disable Storing methods
     * @param ...$storage
     * @return $this
     */
    public function viaExcept(...$storage): self
    {
        $this->storage = array_diff($this->storage, $storage);

        return $this;
    }

    /**
     * Disable Storing methods
     * @param ...$storage
     * @return $this
     */
    public function viaOnly(...$storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return Cacher
     */
    public function viaDatabase(): Databaser
    {
        return new Databaser($this);
    }

    /**
     * @return Cacher
     */
    public function viaCache(): Cacher
    {
        return new Cacher($this);
    }

    /**
     * @param Period $period
     * @return $this
     */
    public function period(Period $period): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->period($period);

        if (in_array("database", $this->storage))
            $this->database = $this->database->period($period);

        return $this;
    }

    /**
     * @param string $column
     * @return $this
     */
    public function unique(string $column = 'unique_id'): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->unique($column);

        if (in_array("database", $this->storage))
            $this->database = $this->database->unique($column);

        return $this;
    }

    /**
     * @param string|null $name
     * @return $this
     */
    public function collection(?string $name = null): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->collection($name);

        if (in_array("database", $this->storage))
            $this->database = $this->database->collection($name);

        return $this;
    }

    /**
     * @param Model|null $user
     * @param bool       $whereMode
     * @return self
     */
    public function visitor(?Model $user = null , bool $whereMode = true): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->visitor($user , $whereMode);

        if (in_array("database", $this->storage))
            $this->database = $this->database->visitor($user , $whereMode);

        return $this;
    }

    /**
     * @param Model|bool|null $post
     * @return self
     */
    public function visitable($post = null): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->visitable($post);

        if (in_array("database", $this->storage))
            $this->database = $this->database->visitable($post);

        return $this;
    }

    /**
     * @return Collection
     */
    public function get(): Collection
    {
        if (in_array("cache", $this->storage))
            $cache = $this->cache->get();
        else
            $cache = collect();

        if (in_array("database", $this->storage))
            $database = $this->database->get();
        else
            $database = collect();

        return $database->merge($cache);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if (in_array("cache", $this->storage))
            $cache = $this->cache->count();
        else
            $cache = 0;

        if (in_array("database", $this->storage))
            $database = $this->database->count();
        else
            $database = 0;

        return $cache + $database;
    }

    /**
     * @return $this
     */
    public function once(): self
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->once();

        if (in_array("database", $this->storage))
            $this->database = $this->database->once();

        return $this;
    }

    /**
     * @param bool       $once
     * @param Model|null $visitable
     * @param Model|null $visitor
     * @return Exception
     */
    public function record(bool $once = false, ?Model $visitable = null, ?Model $visitor = null): Exception
    {
        return new Exception('This method is not Available For General Uses');
    }

    /**
     * @return void
     */
    public function truncate(): void
    {
        if (in_array("cache", $this->storage))
            $this->cache    = $this->cache->truncate();

        if (in_array("database", $this->storage))
            $this->database = $this->database->truncate();
    }

    /**
     * @return void
     */
    public function delete()
    {
        if (in_array("cache", $this->storage))
            $this->cache->delete();

        if (in_array("database", $this->storage))
            $this->database->delete();
    }

}