Getting Start
=============

**Installation**

First begin a new fresh laravel:

`composer create-project --prefer-dist laravel/laravel your_project_name`

And set up the db config in your `.env` file as necessary.

Now, edit the `composer.json` to reference the correct repositories as hosted on GitLab or using the private
package hosting service of your choice. You will have to require the following packages:

 - `dms/web.laravel`
 - `dms/common.structure`
 - `dms/core`
 
And run `composer update dms/*` to load the dms packages.

Now that you have the packages, you have to load the dms service provider:

Put the following line in your `config/app.php` in the `providers` array:

```php
Dms\Web\Laravel\DmsServiceProvider::class,
```

And finally, run the following artisan command:

```
php artisan dms:install
```