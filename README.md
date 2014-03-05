Restify
=======

Rest Helper Package for Laravel 4

## Installation

Add the following lines to your composer.json:

```json
"psr-4": {
  "Ingruz\\": "app/Ingruz"
}
```

Then in your app/config/app.php add:

```php
'Ingruz\Rest\RestServiceProvider',
```

under the services providers and

```php
'Restify'	=> 'Ingruz\Rest\RestFacade',
```

under the aliases.

## Usage

Your models should now extend from Ingruz\Models\RestModel instead of Eloquent.

Then you can create a set of route in this way:

```php
Restify::resource('post');
```

where 'post' is the lower-cased name of your model's class.

Now the following routes have been created:

GET 'post'

GET 'post/{id}'

POST 'post'

PUT 'post/{id}'

DELETE 'post/{id}'