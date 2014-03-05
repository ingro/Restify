<?php namespace Ingruz\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Validator;

use Ingruz\Rest\Helpers\DBQueryHelper as RestQueryBuilder;
use Ingruz\Rest\Exceptions\ValidationErrorException;

abstract class RestModel extends Model {

	protected static $rules = array(
		'save' => array(),
		'create' => array(),
		'update' => array()
	);
	protected $validationErrors;
	// protected $mergedRules = array();

	protected $saved = false;
	protected $valid = false;

	protected static $purgeable = array();
	protected $fullSearchFields = array();
	protected $eagerTables = array();

	public function __construct( array $attributes = array() )
	{
		parent::__construct( $attributes );
		$this->validationErrors = new MessageBag;
	}

	public static function boot()
	{
		parent::boot();

		self::saving(function($model)
		{
			return $model->beforeSave();
		});

		self::saved(function($model)
		{
			return $model->afterSave();
		});
	}

	public function isValid()
	{
		return $this->valid;
	}

	public function isSaved()
	{
		return $this->saved;
	}

	public function save(array $options = array(), $force = false)
	{
		if ( $force || $this->validate() )
		{
			return $this->performSave($options);
		} else
		{
			return false;
		}
	}

	public function scopeListConditions($query)
	{
		return $query;
		// return $query->where('content', '<>', '');
	}

	public function scopeListOrder($query)
	{
		return $query;
		// return $query->orderBy('id');
	}

	public static function getList()
	{
		$istance = new static;
		$builder = new RestQueryBuilder($istance);

		return $builder->getData();
	}

	public function getItem()
	{
		return $this->toRestData();
	}

	public static function createItem($data)
	{
		$istance = static::create($data);

		return $istance;
	}

	public function editItem($data)
	{
		return $this->update($data);
	}

	public function deleteItem()
	{
		return $this->delete();
	}

	public function errors()
	{
		return $this->validationErrors->toArray();
	}

	public function toRestData($list = false)
	{
		return $this->toArray();
	}

	protected function beforeSave()
	{
		return true;
	}

	protected function afterSave()
	{
		return true;
	}

	protected function performSave(array $options) {

		$this->purgeAttributes();

		$this->saved = true;

		return parent::save($options);
	}

	protected function validate()
	{
		$rules = $this->mergeRules();

		if ( empty($rules) ) return true;

		$data = $this->attributes;

		$validator = Validator::make($data, $rules);
		$success = $validator->passes();

		if ( $success )
		{
			if ( $this->validationErrors->count() > 0 )
			{
				$this->validationErrors = new MessageBag;
			}
		} else
		{
			$this->validationErrors = $validator->messages();
			throw new ValidationErrorException($validator->messages());
		}

		$this->valid = true;

		return $success;
	}

	public function getPurgeAttributes()
	{
		return $this->purgeable;
	}

	public function getFullSearchFields()
	{
		return $this->fullSearchFields;
	}

	public function getEagerTables()
	{
		return $this->eagerTables;
	}

	private function mergeRules()
	{
		$rules = static::$rules;
		$output = array();

		if ($this->exists)
		{
			$merged = (isset($rules['update'])) ? array_merge_recursive($rules['save'], $rules['update']) : $rules['save'];
		} else
		{
			$merged = (isset($rules['create'])) ? array_merge_recursive($rules['save'], $rules['create']) : $rules['save'];
		}

		foreach ($merged as $field => $rules)
		{
			if (is_array($rules))
			{
				$output[$field] = implode("|", $rules);
			} else
			{
				$output[$field] = $rules;
			}
		}

		return $output;
	}

	protected function purgeAttributes()
	{
		$attributes = $this->getPurgeAttributes();

		if ( ! empty($attributes) )
		{
			foreach ( $attributes as $attribute )
			{
				unset($this->attributes[$attribute]);
			}
		}
	}
}