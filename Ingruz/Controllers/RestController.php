<?php namespace Ingruz\Controllers;

use BaseController;

class RestController extends BaseController {

	protected $className;

	public function __construct()
	{
		parent::__construct();
		if (empty($this->className))
		{
			$this->className = str_replace('Controller', '', get_class($this));
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$list = call_user_func(array($this->className, 'getList'));
		// return $this->className::getRestList();

		if ($list)
		{
			return $list;
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$item = call_user_func(array($this->className, 'createItem'), Request::all());
		// $item = $this->className::createRestItem(Request::all());

		// if (count($item->errors()) > 0)
		// {
		// 	return Response::json($item->errors(), 400);
		// }

		return $item->getItem();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$item = $this->getItemIstance($id);

		return $item->getItem();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$item = $this->getItemIstance($id);
		$update = $item->editItem(\Input::all());

		// if ( ! $update)
		// {
		// 	return Response::json($item->errors(), 400);
		// }

		return $item->getItem();
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$item = $this->getItemIstance($id);
		$delete = $item->deleteItem();

		$code = ($delete) ? 200 : 400;

		return \Response::json($delete, $code);
	}

	private function getItemIstance($id)
	{
		return call_user_func(array($this->className, 'findOrFail'), $id);
		// return $this->className::findOrFail($id);
	}
}