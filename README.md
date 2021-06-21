![GitHub](https://img.shields.io/github/license/ami-hp/laravel-eye?style=for-the-badge)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/ami-hp/laravel-eye?color=00aa00&label=Release&logo=github&style=for-the-badge)


# Eye : Laravel Visitor Counter
`ami-hp/laravel-eye` has the ability to record your website's **Daily Views** in database by only two queries per day.  The trick is in using **Laravel's Cache** and **Cron Job**.
>  So just remember this: **DO NOT FLUSH THE CACHE**.

## List of contents
- [Laravel Visitor Counter](#laravel-visitor-counter)
- [List of Contents](#list-of-contents)
- [Installation](#Installation)
- [usage](#usage)

## Installation
Install via Composer

`$ composer require ami-hp/laravel-eye`

## Configuration
**Ignore this** If you are using  `Laravel 5.5`  or higher;  you don't need to include the provider and alias. (Skip to b)

1. In your `config/app.php`  file add these two lines.
	```php
	// In your providers array.
	'providers' => [
	    ...
	    Ami\Eye\EyeServiceProvider::class,
	],

	// In your aliases array.
	'aliases' => [
	    ...
	    'Eye' => Ami\Eye\Facade\Eye::class,
	],
	```
2. Publish files via Artisan
	`$ php artisan vendor:publish --provider=Ami\Eye\EyeServiceProvider`

	> #### --tag =
	> 	- eye-migration
	> 	- eye-config
	> 	- eye-command
3. Migrate the Eye Tables
	`$ php artisan migrate`
	> #### tables :
	> 	- eye-total-views
	> 	- eye-detailed-views
	
## Usage 
In order to manage your caches, you should know your pages. Every page (or to be precise Every route) can have it's own cache. So you'll have to :
### 1. Define your caches
in `config\eye.php` you can define your **cache names** , **types** and **cache groups**.
-  **cache names** & **types** are defined as **key** & **value**. You can use types for recording in **Detailed Table**. 
	
	```php
	'cache_types'  => [
		'cache_name_1'  =>  "type_1",
		'cache_name_2'  =>  "type_2",
		'cache_name_3'  =>  "type_3",
		'cache_name_4'  =>  "type_4",
		...
	],
	```
- On the other hand, You can use **cache groups** for finding the Sum of specific caches.
	```php
	'type_groups'  => [
		"cache_group1"  => ['cache_name_1','cache_name_2'],
		"cache_group2"  => ['cache_name_3','cache_name_4'],
		...
	],
	```
	I'll explain more further in methods.

- Optional - You can also change name of the tables by changing values of this array.
	```php
	'tables' => [
        'total'   => "eye_total_views",
        'details' => "eye_detailed_views",
    ],
	```

### 2. Cache Your Viewers
Pick any page and use `watch` method to cache your viewers and in return get the counts of the cache.
```php
/** 
 *  watch(string $cache_name ,  int $id = 0) 
 *  You can use $id for relating a cache/page to a record in database such as a Product or Article
 ** $id = 0 means we are NOT relating this cache/page to any record in database
 **/

$pageViews = watch("cache_name_1"); // independent pages

// OR

$pageViews = watch("cache_name_2" , 5); // related pages

dump($pageViews);

//------------Returns
{
  +"users": 1 // count of IPs in cache('cache_name_2') for id 5
  +"seen": 2 // count of page visits in cache('cache_name_2') for id 5
}




dump(cache("cache_name_1"));

//-----------Returns 
Illuminate\Support\Collection { ▼
  #items: array:1 [▼
    "127.0.0.1" => array:1 [▼
      0 => array:6 [▼
        "ip" => "127.0.0.1"
        "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64)...
        "page_id" => 0
        "page_type" => "type_1"
        "visited_at" => Carbon\Carbon @1624264801 {▶ ...}
        "count" => 2
      ]
    ]
  ]
}
```

### 3. Insert Daily Caches in Database

#### 3-1. via **Terminal**

For Debugging purposes, You can use laravel-eye command written in  `App\Console\Commands\DailyViews.php`

#### 3-2. via **Cron Job**
With Cron Job you don't have to request a query everytime a user loads your webpage. CronJob Does this anytime you want.

All you have to do is :
Go to CronJobs , Add your Artisan file and Execution time then write laravel-eye command written in  `App\Console\Commands\DailyViews.php` in front of it. For instance:

`/usr/local/bin/php /home/my-username/my-project-root-path/artisan eye:record > /dev/null 2>&1`

**Example:**

a. From `CPanel` menu, go to `Cron Jobs`

<div align="center">
	<img src="https://github.com/ami-hp/laravel-eye/blob/main/docs/img/cpanel-cronjob.png?raw=true" style="max-width:500px;border-radius:10px;"/>
</div>


b. Go to `Common Settings`. Set execution time and your command. change **username** and **path-to-base-of-project** to locate your **Artisan** file.

<div align="center">
	<img src="https://github.com/ami-hp/laravel-eye/blob/main/docs/img/cp-cronjob-settings.png?raw=true" style="max-width:500px;border-radius:10px"/>
</div>


Now wait for execution.

### 4. Get your daily records from Database

For each table, there is a method to use.

- eye_total_table : `readyTotalChart`
- eye_detailed_table : `readyDetailsChart`

```php
/**
 *  readyTotalChart(string|array $type = "total" , $timeType = "gregorian",  Boolean $json = true)
 ** $type is the cache_name OR the group_name you set in confing
 ** if $type were string , function will return a single type of records in database
 ** if $type were array  , function will return a summed up result of multiple types of records in database
 ** $timeType can be gregorian or jalili BUT for jalili you'll have to require morilog/jalali
 ** $json is boolean. if false , return will not be json_encode
 **/



/**
 *  readyDetailsChart(string|array $type = "total" , int $page_id = 0 , $timeType = "gregorian", Boolean $json = true)
 *  You can use $page_id for  related cache/page to a record in database such as a Product or Article
 ** $id = 0 means we are NOT relating this cache/page to any record in database
 **/

```