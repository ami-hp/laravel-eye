<?php

namespace Ami\Eye\Traits;

use Ami\Eye\Contracts\UserAgentParser;
use Ami\Eye\Parsers\JenssegersAgent;
use Ami\Eye\Parsers\UAParser;
use Ami\Eye\Models\Visit;
use Ami\Eye\Services\EyeService;
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
    public $config;

    /**
     * Collection Name.
     *
     * @var string
     */
    protected $collection;

    /**
     * parser name.
     *
     * @var string
     */
    protected $parser;

    /**
     * parser instance.
     *
     * @var object
     */
    protected $parserInstance;

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
     * A unique cookie for user
     *
     * @var string
     */
    protected $visitorCookieKey;

    /*
    |--------------------------------------------------------------------------
    | List of parsers
    |--------------------------------------------------------------------------
    |
    |
    | You can create your own parser if you like and add the
    | config in the parsers array and the class to use for
    | here with the same name. You will have to implement
    | Ami\Eye\Contracts\UserAgentParser in your parser.
    |
    */
    public $parsers = [
        'jenssegers' => JenssegersAgent::class,
        'UAParser'   => UAParser::class,
    ];


    /**
     * constructor.
     * @throws Exception
     */
    protected function initializeDataPrepTrait()
    {
        $this->request          = Container::getInstance()->make('request');
        $this->config           = Container::getInstance()->make('config');

        $this->visitorCookieKey = $this->config['eye']['cookie']['key'] ?? "eye__visitor";

        $this->byParser($this->config['eye']['default_parser']);
        $this->setVisitor($this->request->user());
    }


    /**
     * Retrieve visitor (user)
     *
     * @param string|null $name
     * @return EyeService
     */
    public function setCollection(?string $name = null) : self
    {
        $this->collection = $name;

        return $this;
    }

    /**
     * Change the parser on the fly.
     *
     * @param string $parser
     *
     * @return $this
     *
     * @throws Exception
     */
    public function byParser(string $parser) : self
    {
        $this->parser = $parser;
        $this->validateParser();

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
        return $this->getParserInstance()->device();
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
        return $this->getParserInstance()->platform();
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
        return $this->getParserInstance()->browser();
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
        return $this->getParserInstance()->languages();
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
     * @param Model|null $user
     *
     * @return $this
     */
    public function setVisitor(?Model $user = null) : self
    {
        $this->visitor = $user;

        return $this;
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
    public function getVisitorModel() : ?Model
    {
        return $this->visitor;
    }

    /**
     * Retrieve visitor (user)
     *
     * @return Model|null
     */
    public function getVisitableModel() : ?Model
    {
        return $this->visitable;
    }

    /**
     *
     * Prorected Methods
     * ------------
     *
     */

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
        return uniqid('eye|', true).Str::random(80);
    }

    /**
     * Get the expiration in minutes.
     */
    protected function cookieExpirationInMinutes(): int
    {
        return $this->config['eye']['cookie']['expire_time'] ?? 10;
    }

    /**
     * Retrieve current parser instance or generate new one.
     *
     * @return mixed|object
     *
     * @throws Exception
     */
    protected function getParserInstance()
    {
        if (!empty($this->parserInstance)) {
            return $this->parserInstance;
        }

        return $this->getFreshParserInstance();
    }

    /**
     * Get new parser instance
     *
     * @throws Exception
     */
    protected function getFreshParserInstance()
    {
        $this->validateParser();

        $parserClass = $this->parsers[$this->parser];

        return Container::getInstance()->make($parserClass);
    }

    /**
     * Validate parser.
     *
     * @throws Exception
     */
    protected function validateParser()
    {
        if (empty($this->parser)) {
            throw new Exception('Parser not selected or default parser does not exist.');
        }

        $parserClass = $this->parsers[$this->parser];

        if (empty($parserClass) || !class_exists($parserClass)) {
            throw new Exception('Parser not found in config file. Try updating the package.');
        }

        $reflect = new ReflectionClass($parserClass);

        if (!$reflect->implementsInterface(UserAgentParser::class)) {
            throw new Exception("Parser must be an instance of Contracts\Parser.");
        }
    }

    /**
     * Private Methods
     * --------------
     */

    /**
     * deprecated
     * -----
     * Retrieve SessionId.
     * no secure for cache.
     * better to use for db or redis
     *
     */
    private function sessionId(): ?string
    {
        return Request::getSession()->getId();
    }
}