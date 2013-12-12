<?php namespace Iyoworks\Entity\Database;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class EntityBuilder extends  EloquentBuilder {
    protected $useModel;

    /**
     * Create a new Eloquent query builder instance.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @param bool $useModel
     * @return \Iyoworks\Entity\Database\EntityBuilder
     */
    public function __construct(QueryBuilder $query, $useModel = true)
    {
        parent::__construct($query);
        $this->useModel = (bool) $useModel;
    }

    /**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function get($columns = array('*'))
	{
		$models = $this->getModels($columns);

		// If we actually found models we will also eager load any relationships that
		// have been specified as needing to be eager loaded, which will solve the
		// n+1 query issue for the developers to avoid running a lot of queries.
		if (count($models) > 0)
		{
			$models = $this->eagerLoadRelations($models);
		}

		if ($this->model instanceof EntitableInterface && !$this->useModel)
		{
			$entities = $this->getEntities($models);
			return $this->model->newEntityCollection($entities);
		}

		return $this->model->newCollection($models);
	}

	/**
	 * @param $models
	 * @return array
	 */
	protected function getEntities($models)
	{
		$entities = [];
		foreach($models as $k => $model)
		{
			$entities[$k] = $this->model->buildEntity($model->getAttributes(), $model->getRelations());
		}
		return $entities;
	}
}
