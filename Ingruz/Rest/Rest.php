<?php namespace Ingruz\Rest;

use Illuminate\Support\ServiceProvider;

class Rest {

	public function resource($path, $controller = null)
	{
		$controller = $controller ?: ucfirst($path).'Controller';

		return \Route::resource($path, $controller, $this->getMethods());
	}

	private function getMethods()
	{
		return array('only' => array(
			'index',
			'show',
			'store',
			'update',
			'destroy'
		));
	}
}