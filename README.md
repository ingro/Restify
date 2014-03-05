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

'Ingruz\Rest\RestServiceProvider',

under the services providers and

'Restify'	=> 'Ingruz\Rest\RestFacade',

under the aliases.
