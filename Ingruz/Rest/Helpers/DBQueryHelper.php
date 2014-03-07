<?php namespace Ingruz\Rest\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Ingruz\Rest\Exceptions\OperandNotFoundException;

class DBQueryHelper {

	protected $item;
	protected $query;
	protected $page = 1;
	protected $perPage = 20;

	protected $operands = array(
		'gt' => '>',
		'gte' => '>=',
		'lt' => '<',
		'lte' => '<=',
		'like' => 'LIKE'
	);

	public function __construct( \Ingruz\Models\RestModel $istance )
	{
		$this->item = $istance;

		if (Request::get('currentPage'))
		{
			$this->page = (int) Request::get('currentPage');
		}

		$top = Request::get('top');

		if ( ! empty($top) )
		{
			$this->perPage = Request::get('top');
		}
	}

	public function getData()
	{
		$this->buildQuery();

		$total = $this->getItemsTotal();

		if ( $total === 0 )
		{
			return false;
		}

		$idsList = $this->getItemsId();

		return $this->getItemsModels($idsList, $total);
	}

	protected function buildQuery()
	{
		// $this->query = DB::table($this->item->getTable());

		$staticItem = get_class($this->item);
		$this->query = $staticItem::listConditions();

		if(Request::get('filter'))
		{
			$term = Request::get('filter');

			$fields = $this->item->getFullSearchFields();

			$this->query->where(function($q) use ($fields, $term)
			{
				foreach ($fields as $field)
				{
					$q->orWhere($field, 'LIKE', '%'.$term.'%');
				}
			});
		}

		if(Request::get('query'))
		{
			$fields = $this->getQueryFields();

			foreach( $fields as $field )
			{
				$this->addQueryFilter($field);
			}
		}

		$this->setItemsOrder($this->query);
	}

	protected function getItemsTotal()
	{
		return (int) $this->query->count($this->item->getKeyName());
	}

	private function getItemsId()
	{
		return $this->query->forPage($this->page, $this->perPage)->lists($this->item->getKeyName());
	}

	private function getItemsModels($ids, $total)
	{
		$data = array(
			'total' => $total,
			'values' => array(),
			'page' => $this->page
			// 'query' => array()
		);

		$staticItem = get_class($this->item);
		$query = $staticItem::whereIn($this->item->getKeyName(), $ids);
		$this->setItemsOrder($query);

		$eagerTables = $this->item->getEagerTables();

		if( ! empty($eagerTables) )
		{
			$query->with($eagerTables);
		}

		$models = $query->get();

		$models->each(function($model) use (&$data)
		{
			array_push($data['values'], $model->toRestData(true));
		});

		return $data;
	}

	protected function getQueryFields()
	{
		return explode('::', Request::get('query'));
	}

	protected function addQueryFilter($chunk)
	{
		if ( $chunk !== "" )
		{
			$sub = explode('||', $chunk);

			if ( count($sub) === 2 )
			{
				$this->addEqualCondition($sub);
			} else if ( count($sub) === 3 )
			{
				$this->addOtherCondition($sub);
			}
		}
	}

	protected function setItemsOrder($query)
	{
		if(Request::get('orderby'))
		{
			$orderField = Request::get('orderby');
			$orderDir = Request::get('orderdir') ? Request::get('orderdir') : 'asc';

			$query->orderBy($orderField, $orderDir);
		} else
		{
			$query->listOrder();
		}
	}

	protected function addEqualCondition($chunk)
	{
		if( $chunk[1] !== "" ) $this->query->where($chunk[0], $chunk[1]);
	}

	protected function addOtherCondition($chunk)
	{
		try
		{
			$operand = $this->getOperand($chunk[1]);
		} catch (OperandNotFoundException $e)
		{
			return false;
		}

		if( $chunk[2] !== "" )
		{
			$value = ($operand === 'LIKE') ? '%'.$chunk[2].'%' : $chunk[2];

			$this->query->where($chunk[0], $operand, $value);
		}
	}

	protected function getOperand($code)
	{
		if ( ! array_key_exists(strtolower($code), $this->operands))
		{
			throw new OperandNotFoundException("Invalid operand found in request's querystring: '{$code}'");
		}

		return $this->operands[$code];
	}
}