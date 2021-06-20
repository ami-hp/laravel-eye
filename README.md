
# Laravel Visitor Counter
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

1. In your  `config/app.php`  file add these two lines.
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
Pick Any page and use `setAndGetViews` method to cache your viewers and in return get the counts of the cache set.
```php
/** 
 *  setAndGetViews(string $cache_name ,  int $id = 0) 
 *  You can use $id for relating a cache/page to a record in database such as a Product or Article
 ** $id = 0 means we are NOT relating this cache/page to any record in database
 * */

$pageViews = setAndGetViews("cache_name_1"); // independent pages

$pageViews = setAndGetViews("cache_name_2" , 5); // related pages

//$pageViews returns : -------

```
To acually see what is in your cache...(in development)