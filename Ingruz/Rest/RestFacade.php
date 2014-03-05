<?php namespace Ingruz\Rest;

use Illuminate\Support\Facades\Facade;

class RestFacade extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'rest'; }
}