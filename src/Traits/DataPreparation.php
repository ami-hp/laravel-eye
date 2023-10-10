<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Contracts\UserAgentParser;
use Ami\Eye\Drivers\JenssegersAgent;
use Ami\Eye\Drivers\UAParser;
use Ami\Eye\Models\Visit;
use Ami\Eye\Services\EyeService;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use ReflectionClass;

trait DataPreparation
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Collection Name.
     *
     * @var string
     */
    protected $collection;

    /**
     * Driver name.
     *
     * @var string
     */
    protected $driver;

    /**
     * Driver instance.
     *
     * @var object
     */
    protected $driverInstance;

    /**
     * Request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * Visitor (user) instance.
     *
     * @var Model|null
     */
    protected $visitor;

    /**
     * Visitable (post) instance.
     *
     * @var Model|null
     */
    protected $visitable;

    /**
     * The Cache Key where the views will be stored
     *
     * @var string
     */


    /**
     * Type of storing the views
     * cache, database
     *
     * @var string
     */
    protected $storage;

    /**
     * A unique cookie for user
     *
     * @var string
     */
    protected $visitorCookieKey;

    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    |
    | You can create your own driver if you like and add the
    | config in the drivers array and the class to use for
    | here with the same name. You will have to implement
    | Ami\Eye\Contracts\UserAgentParser in your driver.
    |
    */
    public $drivers = [
        'jenssegers' => JenssegersAgent::class,
        'UAParser'   => UAParser::class,
    ];


    /**
     * EyeService constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->request          = Container::getInstance()->make('request');
        $this->config           = Container::getInstance()->make('config');
        $this->visitorCookieKey = $this->config['eye']['cookie']['key'] ?? "eye__visitor";

        $this->viaDriver($this->config['eye']['default_driver']);
        $this->setVisitor($this->request->user());
    }


    /**
     * Change the driver on the fly.
     *
     * @param string $driver
     *
     * @return $this
     *
     * @throws Exception
     */
    public function viaDriver(string $driver) : self
    {
        $this->driver = $driver;
        $this->validateDriver();

        return $this;
    }

    /**
     * Retrieve request's data
     *
     * @return array
     */
    public function request() : array
    {
        return $this->request->all();
    }

    /**
     * Retrieve user's ip.
     *
     * @return string|null
     */
    public  function ip() : ?string
    {
        return $this->request->ip();
    }

    /**
     * Retrieve request's url
     *
     * @return string
     */
    public function url() : string
    {
        return $this->request->fullUrl();
    }

    /**
     * Retrieve request's referer
     *
     * @return string|null
     */
    public function referer() : ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }

    /**
     * Retrieve request's method.
     *
     * @return string
     */
    public function method() : string
    {
        return $this->request->getMethod();
    }

    /**
     * Retrieve http headers.
     *
     * @return array
     */
    public function httpHeaders() : array
    {
        return $this->request->headers->all();
    }

    /**
     * Retrieve agent.
     *
     * @return string
     */
    public function userAgent() : string
    {
        return $this->request->userAgent() ?? '';
    }

    /**
     * Retrieve device's name.
     *
     * @return string
     *
     * @throws Exception
     */
    public function device() : string
    {
        return $this->getDriverInstance()->device();
    }

    /**
     * Retrieve platform's name.
     *
     * @return string
     *
     * @throws Exception
     */
    public function platform() : string
    {
        return $this->getDriverInstance()->platform();
    }

    /**
     * Retrieve browser's name.
     *
     * @return string
     *
     * @throws Exception
     */
    public function browser() : string
    {
        return $this->getDriverInstance()->browser();
    }

    /**
     * Retrieve languages.
     *
     * @return array
     *
     * @throws Exception
     */
    public function languages() : array
    {
        return $this->getDriverInstance()->languages();
    }

    /**
     * Retrieve SessionId.
     * no secure for cache.
     * better to use for db or redis
     * @return ?string
     */
    public function sessionId(): ?string
    {
        return Request::getSession()->getId();
    }

    /**
     * Retrieve visitor (user)
     *
     * @param string|null $collection
     * @return EyeService
     */
    public function collection(?string $collection = null) : self
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Get the unique ID that represents the visitor.
     * @throws BindingResolutionException
     */
    public function uniqueId(): string
    {

        if (! Cookie::has($this->visitorCookieKey)) {
            $uniqueString = $this->generateUniqueCookieValue();

            Cookie::queue($this->visitorCookieKey, $uniqueString, $this->cookieExpirationInMinutes());

            return $uniqueString;
        }

        return Cookie::get($this->visitorCookieKey);
    }

    /**
     * Set visitor (user)
     *
     * @param Model|null $user
     *
     * @return $this
     */
    protected function setVisitor(?Model $user = null) : self
    {
        $this->visitor = $user;

        return $this;
    }

    /**
     * Retrieve visitor (user)
     *
     * @return Model|null
     */
    protected function getVisitorModel() : ?Model
    {
        return $this->visitor;
    }

    /**
     * @throws Exception
     */
    public function getCurrentVisit(): Visit
    {
        $data = $this->prepareLog();

        return new Visit($data);
    }

    /**
     * Set visitor (user)
     *
     * @param Model|null $post
     * @return $this
     */
    public function setVisitable(?Model $post = null) : self
    {
        $this->visitable = $post;

        return $this;
    }

    /**
     * Retrieve visitor (user)
     *
     * @return Model|null
     */
    protected function getVisitableModel() : ?Model
    {
        return $this->visitable;
    }


    /**
     * Prepare log's data.
     *
     * @return array
     *
     * @throws Exception
     */
    protected function prepareLog() : array
    {
        return [
            'ip'           => $this->ip(),
            'url'          => $this->url(),
            'method'       => $this->method(),
            'device'       => $this->device(),
            'browser'      => $this->browser(),
            'request'      => $this->request(),
            'referer'      => $this->referer(),
            'platform'     => $this->platform(),
            'unique_id'    => $this->uniqueId(),
            'languages'    => $this->languages(),
            'useragent'    => $this->userAgent(),
            'collection'   => $this->collection,
            'headers'      => $this->httpHeaders(),
            'visitable_id'   => $this->getVisitableModel() ? $this->getVisitableModel()->id : null,
            'visitable_type' => $this->getVisitableModel() ? get_class($this->getVisitableModel()) : null,
            'visitor_id'     => $this->getVisitorModel() ? $this->getVisitorModel()->id : null,
            'visitor_type'   => $this->getVisitorModel() ? get_class($this->getVisitorModel()) : null,
            'viewed_at'      => Date::now(),
        ];
    }

    /**
     * Generate a unique visitor id.
     */
    protected function generateUniqueCookieValue(): string
    {
        return uniqid('eye|', true).Str::password(80);
    }

    /**
     * Get the expiration in minutes.
     * @throws BindingResolutionException
     */
    protected function cookieExpirationInMinutes(): int
    {
        return $this->config['cookie']['expire_time'];
    }

    /**
     * Retrieve current driver instance or generate new one.
     *
     * @return mixed|object
     *
     * @throws Exception
     */
    protected function getDriverInstance()
    {
        if (!empty($this->driverInstance)) {
            return $this->driverInstance;
        }

        return $this->getFreshDriverInstance();
    }

    /**
     * Get new driver instance
     *
     * @throws Exception
     */
    protected function getFreshDriverInstance()
    {
        $this->validateDriver();

        $driverClass = $this->drivers[$this->driver];

        return Container::getInstance()->make($driverClass);
    }

    /**
     * Validate driver.
     *
     * @throws Exception
     */
    protected function validateDriver()
    {
        if (empty($this->driver)) {
            throw new Exception('Driver not selected or default driver does not exist.');
        }

        $driverClass = $this->drivers[$this->driver];

        if (empty($driverClass) || !class_exists($driverClass)) {
            throw new Exception('Driver not found in config file. Try updating the package.');
        }

        $reflect = new ReflectionClass($driverClass);

        if (!$reflect->implementsInterface(UserAgentParser::class)) {
            throw new Exception("Driver must be an instance of Contracts\Driver.");
        }
    }
}