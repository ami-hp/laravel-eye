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
	    'Payment' => Ami\Eye\Facade\Eye::class,
	],
	```
2. Publish files via Artisan
	`$ php artisan vendor:publish --provider=Ami\Eye\EyeServiceProvider`

	> --tag =
	> 	- migration
	> 	- config
	> 	- command
	
## Usage
How To Use Laravel-Eye : 


