# Laravel-Eye
A Php >=7.2 Package for Laravel.

This Package is a combination of two visit counter packages :
- [shetabit/visitor](https://github.com/shetabit/visitor)
- [cyrildewit/eloquent-viewable](https://github.com/cyrildewit/eloquent-viewable)
    - and a little extra.

It Stores Each Visit based on user's **Cookie**.
The Idea is being able to Cache Visits to reduce queries.
Using Cache is better for Websites with a little higher than normal traffic.

|             | Speed  |
|-------------|--------|
| Database    | Low    | 
| Cache:file  | Medium | 
| Cache:redis | High		 | 

> **NOTE** : If you save a high amount of data in cache, memory WILL BE EXHAUSTED. The Limitation Depends on your memory but no more than 1 million is recommended to save in cache.

These paths are provided for you to store the Visits When User Comes to your Visitable Page:

```mermaid
graph LR
A{Client Side} -- record --> B(via Cache)

B  -- push --> D(via Database)
A  -- record --> D
```

And These paths are provided to **get** the Visits from your storage:
```mermaid
graph LR
A(via Database) --> D{Client Side}
B(via Cache)  --> D{Client Side}

A --> E((SUM))
B --> E((SUM)) 

E --> D
```

# Install

```winbatch
$ composer require ami-hp/laravel-eye
$ php artisan vendor:publish --provider="Ami\Eye\Providers\EyeServiceProvider"
$ php artisan migrate
```
> **NOTE** : It is recommended to migrate the default __jobs__ and __failed_jobs__ tables that come with a fresh laravel project, too.

# Config
#### After Publishing the package files, you will have an `eye.php` file in your config folder.
1. Here you can define the name of your visits table.

   ```php
   'table_name' => 'eye_visits',
   ```
2. To Prevent getting Memory Errors, You can specify the maximum amount of Visits saved in cache. after reaching to the maximum, all the Visits will be inserted to database. Also, you can change your cache key, too.
   ```php
   'cache' => [  
       'key' => 'eye__records',  
       'max_count' => 1000000,  
   ],
   ```

3. A Cookie will be set for the user when arrives to your page. You can Change the key of your cookie anytime. The Expiration Time is set for 5 years, you can change that as well.
   ```php
   'cookie' =>[  
       'key' => 'eye__visitor',  
       'expire_time' => 2628000, //in minutes aka 5 years  
   ],
   ```
4. The Package uses two packages to parse user agents: **jenssegers/agent** and **ua-parser/uap-php** . On default it is set for **jenssegers**. You Can Change it to  **UAParser**.
   ```php
   'default_parser' => 'jenssegers',
   ```
5. You can decide to store crawlers visits or not. The Package will use **jaybizzle/crawler-detect** to detect crawlers.
   ```php
   'ignore_bots' => true,
   ```
6. If you wanted to use Jobs to increase speed in your inserts the package will do it for you. Feel free to turn it to false any time.
   ```php
   'queue' => true,
   ```

# Migration

```php
   Schema::create(config('eye.table_name'), function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->mediumText('unique_id')->nullable();
            $table->string('method')->nullable();
            $table->mediumText('request')->nullable();
            $table->mediumText('url')->nullable();
            $table->mediumText('referer')->nullable();
            $table->text('languages')->nullable();
            $table->text('useragent')->nullable();
            $table->text('headers')->nullable();
            $table->text('device')->nullable();
            $table->text('platform')->nullable();
            $table->text('browser')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('collection')->nullable();
            $table->nullableMorphs('visitable'); // object model
            $table->nullableMorphs('visitor'); // subject model
            $table->timestamp('viewed_at')->useCurrent();
        });
```
-------
# Methods

### To Set Visit Model Data

```php

//To set visitable model
$eye = eye($visitable);
//OR 
$eye = eye()->setVisitable();
//And there are other ways
----------------------------
//to Change Visitor Model
//It is set to auth()->user() on default
$eye = eye()->setVisitor($userModel);
//And there are other ways
----------------------------
//To set Collection
$eye = eye()->setCollection($string);
----------------------------
//To change the parser value in config
$eye = eye()->byParser($string);
----------------------------
```

### To Get Visit Model Data
```php
eye()->request(); // == request()->all()
eye()->ip(); // == request()->ip()
eye()->url(); // == request()->fullUrl()
eye()->referer(); // == $_SERVER['HTTP_REFERER'] ?? null
eye()->method(); // == request()->getMethod()
eye()->httpHeaders(); // == request()->headers->all()
eye()->userAgent(); // == request->userAgent() ?? ''
eye()->device(); //example: Webkit, ...
eye()->platform(); //example: Windows, Mac, ...
eye()->browser(); //example: Edge, Chrome, ...
eye()->languages(); //example: ["en-us","en","fa"]
eye()->uniqueId(); // == Str::random for cookie
eye()->getCurrentVisit(); // All the above as Visit Model
eye()->getVisitorModel(); // example: User Model
eye()->getVisitableModel(); //example: Article Model
```

### Choose Your Storing method
> **NOTE**: If you don't use these methods, the package automatically uses all of storages
```php
$eye = $eye->viaCache(); // meaning : only using cache
$eye = $eye->viaDatabase(); // meaning : only using database
```
You Can Also Combine the result of storages that you choose:
```php 
eye()->via('cache' , 'database'); // meaning : only using database
// or simply
eye()
```

## General Methods (Interface)
These Methods Work on `viaDatabase` and `viaCache` and any storing method that this package provides.
### 1. period(Period $period)
You can read all of Period Documentation in this Link: 
[Cryildewit/Period](https://github.com/cyrildewit/eloquent-viewable/blob/master/README.md#between-two-datetimes)

```php
//basic example
//The Date Should be at least Y-m
$period = Period::create('2017-04-20', '2023-07-08');
$eye = $eye->period($period);

```
### 2. unique(string $column = 'unique_id')
This chain method will assist to get Unique values base on the column you need.
* By default, it is set for unique_id .

```php
//example
$eye = $eye->unique('browser');
```

### 3. collection(?string $name = null)
This chain method can help to get Visits or set collection for `Visit` Model.
```php
//example
$eye = $eye->collection('articles');
```

### 4. visitor(?Model $user = null , bool $whereMode = true)
This chain method will replace visitor fields in `Visit` Model.
* It is useful for getting the User's activity.
By default `$whereMode` in true which means :
Everytime you are fetching data it will use `->where('visitor_type' , $type)->where('visitor_id' , $id)` methods to get them. 
```php
//example
$user = User::findOrFail(1);

$eye = $eye->visitor($user);
//or
$eye = $eye->visitor($user , false); // to disable where() query methods
```


### 5. visitable(Model|null|bool $post = null)
This chain method has two usages based on Argument:
1. **Model or Null** : It will Replace value of `visitable_type` and `visitable_id` in `Visit` Model. For Fetching visits it will use `->where('visitable_type' , $type)->where('visitable_id' , $id)` 
2. **Bool** False : IF ONLY false was given it will disable `where()` methods
```php
//example
$post = Article::findOrFail(1);

$eye = $eye->visitable($post);
//or
$eye = $eye->visitable(false); // to disable where() query methods
```
> **NOTE**: If where() is enabled and visitable is null , the packages works based on url.

### 6. get()
It will Fetch Visits from database or cache with the help of the queries you specified (like above).
```php
//example
$eye = $eye->get();
```

### 7. count()
It does the exact thing as `get()` does. but returns Integer.
```php
//example
$eye = $eye->count();
```
> **NOTE:** If you don't specify one storing method ,`get()`and`count()` will fetch data from multiple storages and combines them.
> 
> **example:**
> If `eye()->viaCache()->count()` returns 6 and `eye()->viaDatabase()->count()` returns 50, `eye()->count()` will return 56. 


### 8. once()
This method will check if the visitor has a record from before, it will not record another one until the user's cookie expires.
> **Note:** for `viaDatabase()` it only works with `count()` and not `get()`
```php
//example
$eye = $eye->once();
```

### 9. record(bool $once = false , ?Model $visitable = null, ?Model $visitor = null)
It will record the visit **ONLY** via the storing method you chose.
you can also:
- replace `once()` with its argument
- replace visitable methods with its argument
- replace visitor methods with its argument
```php
//simple example
$eye->record();

//or

$post = Post::findOrFail(1);
$user = User::findOrFail(1);
$eye->record(
        once      : true,  // == once()
        visitable : $post, // == ->setVisitable($post)
        visitor   : $user, // == ->setVisitor($user)
    ); 
```
### 10. truncate()
removes all visits in storage
```php
$eye->truncate();
```
### 11. delete()
removes all visits in storage
```php
$eye->delete();
```

## Exclusive Methods
### Cache methods
#### 1. forget(): bool
```php
eye()->viaCache()->forget();
//equals to this
Cache::forget(config('eye.cache.key'));
```
#### 2. pushCacheToDatabase() : bool
You can push caches to database anytime you want
```php
eye()->viaCache()->pushCacheToDatabase();

// it uses Visit::insert($cachedVisits)
```
#### 3. Macro methods
This package automatically binds methods to Collection class
```php
$collection = Cache::get(config('eye.cache.key'));

$collection->period(Period $period);
$collection->whereUrl(string $url);
$collection->whereVisitor(?Model $user);
$collection->whereVisitable(?Model $post);
$collection->whereCollection(string $name);
$collection->whereVisitHappened(Visit $visit);
```
### Database methods
#### 1. queryInit()
You can write any query you want.
```php
eye()->viaDatabase()->queryInit();
//equals to this
Visit::query();
```


# Usage

## Recording Visits

> **Note:** *Recording Methods are applied for all storing methods, Therefore you can replace `viaCache` with other ones*.
> > The only Method that does not have General Usage is `Record()`. It just didn't make sense to make one. Feel free comment if you needed.

### 1. Record only for Url
If you don't set visitable you can work with url
```php
//only Individual Usage
eye()->viaCache()->record();
```

### 2. Record for Visitable Model
 
```php
$post = Post::findOrFail(1);

//All of these lines return the same outcome.

//Individual Usage
eye($post)->viaCache()->record(); //Recommended
//or
eye()->setVisitable($post)->viaCache()->record();
//or
eye()->viaCache()->visitable($post)->record();
//or
eye()->viaCache()->record(visitable: $post);
```

### 3. Record with new Visitor Model
 It will replace `auth()->user()`
```php
$user = User::findOrFail(1);

//All of these lines return the same outcome.

eye()->setVisitor($user)->viaCache()->record();
//or
eye()->viaCache()->visitor($user)->record(); //recommended
//or
eye()->viaCache()->record(visitor: $user);
```

### 4. Record with Collection

 It will fill collection column

```php
$name = "name of collection";

//All of these lines return the same outcome.

eye()->setCollection($name)->viaCache()->record(); 
//or
eye()->viaCache()->collection($name)->record(); //Recommended
```

### 5. Record only once for a cookie
 
```php
//All of these lines return the same outcome.

eye()->viaCache()->once()->record(); //Recommended 
//or
eye()->viaCache()->record(true);
```
## Fetching Visits

### 1. Get/Count Where IS "Current Url"

```php
//General Usage
eye()->get();
-------------  
eye()->count();

//Individual Usage
eye()->viaCache()->get();
-------------  
eye()->viaCache()->count();

```
### 2. Get/Count Where IS "Visitable Model"

```php
$post = Post::findOrFail(1);

//General Usage
eye($post)->get(); // Recommended
//or  
eye()->visitable($post)->get();
//or  
eye()->setVisitable($post)->get();
-------------
eye($post)->count(); // Recommended
//or  
eye()->visitable($post)->count();
//or
eye()->setVisitable($post)->get();


//Individual Usage
eye($post)->viaCache()->get(); // Recommended
//or  
eye()->viaCache()->visitable($post)->get();
//or  
eye()->setVisitable($post)->viaCache()->get();
-------------
eye($post)->viaCache()->count(); // Recommended
//or  
eye()->viaCache()->visitable($post)->count();
//or
eye()->setVisitable($post)->viaCache()->get();

```


### 3. Get/Count Where IS "Visitor Model"

```php
$user = User::findOrFail(1);// Null works too

//General Usage
eye()->visitor($user)->get();
-------------
eye()->visitor($user)->count();

//Individual Usage
eye()->viaCache()->visitor($user)->get();
-------------
eye()->viaCache()->visitor($user)->count();
```

### 4. Get/Count Where IS NOT "Visitor Model"

```php
$user = User::findOrFail(1);// Null works too

//General Usage
eye()->visitor($user, false)->get();
-------------
eye()->visitor($user, false)->count();

//Individual Usage
eye()->viaCache()->visitor($user, false)->get();
-------------
eye()->viaCache()->visitor($user, false)->count();
```

### 5. Get/Count Where IS "Collection"

```php
//General Usage
eye()->collection('name of collection')->get();
-------------
eye()->collection('name of collection')->count();

//Individual Usage
eye()->viaCache()->collection('name of collection')->get();
-------------
eye()->viaCache()->collection('name of collection')->count();
```

### 6. Get/Count Where IS "Period"
You can read all of Period Documentation in this Link:
[Cryildewit/Period](https://github.com/cyrildewit/eloquent-viewable/blob/master/README.md#between-two-datetimes)
```php
$period = Period::create('2011-10' , '2023-10-17 12:00:00');// Null works too

//General Usage
eye()->period($period)->get();
-------------
eye()->period($period)->count();

//Individual Usage
eye()->viaCache()->period($period)->get();
-------------
eye()->viaCache()->period($period)->count();
```

### 7. Get/Count Where IS "Unique"
> **NOTE:** This method is the only method that does not work for database `get()`
It is best to use `count()`  
```php
$field = "column name";// Null works too

//eye()->unique($field)->get(); //DOES NOT WORK properly
//eye()->viaDatabase()->unique($field)->get() //DOES NOT WORK
eye()->viaCache()->unique($field)->get() //WORKS
-------------
eye()->unique($field)->count();// recommended
eye()->viaDatabase()->unique($field)->count();// WORKS
eye()->viaCache()->unique($field)->count();// WORKS
```

## Removing Visits
### 1. Remove ALL
Removes Everything, Everywhere, All At Once
```php
//General Usage
eye()->truncate();

//Individual Usage
eye()->viaCache()->truncate();
```
### 1. Remove Selected Visits
Select the Visits by methods above then delete. examples:
```php
//General Usage
eye()->collection($name)->visitable($post)->delete();

//Individual Usage
eye()->viaCache()->collection($name)->visitable($post)->delete();

```

----------
## How To Use Traits
You can also get visits data through you morphed models.

### EyeVisitable Trait
Add `EyeVisitable` to your visitable model.

#### Observer
Visitable observer will monitor your model's activity so,
when your model got (force) deleted visits
gets deleted as well by this code:

```php
public function deleted(Model $visitable)
{
    if ($visitable->isForceDeleting())
        eye($visitable)->delete();
}
```

#### Relations
In order to use eager loading you will need database relationships.
```php
public function visits()
{
    return $this->morphMany(Visit::class, 'visitable');
}
```
Usage examples:

```php
$posts = Post::with('visits')->get();

foreach ($posts as $post){
    $post->visits; // collection of visits
}

//OR

$posts = Post::withCount('visits')->get();

foreach ($posts as $post){
    $post->visits_count; // int
}
```
As you know, morphMany relationships are related to database,
so somehow we need to access cached visits as well.
this method also has been added to trait to access cached visits more easily.
```php
public function cachedVisits(?string $unique = null , ?string $collection = null , ?Model $visitor = null): Collection  
```
Usage examples:
```php
$visits = $post->cachedVisits(); //collection of visits
// OR
$visits = $post->cachedVisits('unique_id' , 'name of collection' , $user);


$visits->count(); //int
```

### EyeVisitor Trait
Add `EyeVisitor` to your visitor model. and the rest is similar to visitable trait.

#### Observer
```php
public function deleted(Model $visitor)
{
    if ($visitor->isForceDeleting())
        eye()->visitor($visitor)->visitable(false)->delete();
}
```

#### Relations
```php
public function visits()
{
    return $this->morphMany(Visit::class, 'visitor');
}
```
```php
public function cachedVisits(?string $unique = null , ?string $collection = null , $visitable = false): Collection
```