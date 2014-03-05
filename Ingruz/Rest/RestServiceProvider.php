<?php namespace Ingruz\Rest;

use Illuminate\Support\ServiceProvider;

class RestServiceProvider extends ServiceProvider {

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['rest'] = $this->app->share(function() { return new Rest; });
	}
}