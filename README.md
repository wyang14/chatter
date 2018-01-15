
# Laravel Forum Package - Chatter

### Installation

Quick Note: If this is a new project, make sure to install the default user authentication provided with Laravel. `php artisan make:auth`

1. Include the package in your project

    ```
    composer require "wyang14/chatter=0.3.*"
    ```

2. Add the service provider to your `config/app.php` providers array:

   **If you're installing on Laravel 5.5+ skip this step**

    ```
    Wyang14\Chatter\ChatterServiceProvider::class,
    ```

3. Publish the Vendor Assets files by running:

    ```
    php artisan vendor:publish --provider="Wyang14\Chatter\ChatterServiceProvider"
    ```

4. Now that we have published a few new files to our application we need to reload them with the following command:

    ```
    composer dump-autoload
    ```

5. Run Your migrations:

    ```
    php artisan migrate
    ```

    Quick tip: Make sure that you've created a database and added your database credentials in your `.env` file.

6. Lastly, run the seed files to seed your database with a little data:

    ```
    php artisan db:seed --class=ChatterTableSeeder
    ```

7. Inside of your master.blade.php file include a header and footer yield. Inside the head of your master or app.blade.php add the following:

    ```
    @yield('css')
    ```

    Then, right above the `</body>` tag of your master file add the following:

    ```
    @yield('js')
    ```

Now, visit your site.com/forums and you should see your new forum in front of you!

### Upgrading

Make sure that your composer.json file is requiring the latest version of chatter:

```
"wyang14/chatter": "0.3.*"
```

Then you'll run:

```
composer update
```

Next, you may want to re-publish the chatter assets, chatter config, and the chatter migrations by running the following:

```
php artisan vendor:publish --tag=chatter_assets --force
php artisan vendor:publish --tag=chatter_config --force
php artisan vendor:publish --tag=chatter_migrations --force
```

Next to make sure you have the latest database schema run:

```
php artisan migrate
```

And you'll be up-to-date with the latest version :)

### Markdown editor

If you are going to make use of the markdown editor instead of tinymce you will need to change that in your config/chatter.php:

```
'editor' => 'simplemde',
```

In order to properly display the posts you will need to include the  `graham-campbell/markdown` library for Laravel:

```
composer require graham-campbell/markdown
```

### Trumbowyg editor

If you are going to use Trumbowyg as your editor of choice you will need to change that in your config/chatter.php:

```
'editor' => 'trumbowyg',
```

Trumbowyg requires jQuery >= 1.8 to be included.

When you published the vendor assets you added a new file inside of your `config` folder which is called `config/chatter.php`. This file contains a bunch of configuration you can use to configure your forums

### Customization

*CUSTOM CSS*

If you want to add additional style changes you can simply add another stylesheet at the end of your `@yield('css')` statement in the head of your master file. In order to only load this file when a user is accessing your forums you can include your stylesheet in the following `if` statement:

```
@if(Request::is( Config::get('chatter.routes.home') ) || Request::is( Config::get('chatter.routes.home') . '/*' ))
    <!-- LINK TO YOUR CUSTOM STYLESHEET -->
    <link rel="stylesheet" href="/assets/css/forums.css" />
@endif
```
