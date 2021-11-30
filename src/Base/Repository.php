<?php namespace Nerio\LaravelStub\Base;

use CaoJiayuan\LaravelApi\Database\Eloquent\Helpers\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @author caojiayuan
 */
class Repository extends \CaoJiayuan\LaravelApi\Http\Repository\Repository
{
  use Filterable;

  public $defaultPageSize = 20;

  protected function getDefaultPageSize()
  {
    return $this->defaultPageSize;
  }

  protected function withModel()
  {
    throw new \BadMethodCallException('withModel method did not implemented');
  }

  protected function modelOf()
  {
    $model = $this->withModel();

    return $this->getModelInstance($model);
  }

  public function destroy($ids)
  {
    return $this->modelOf()->destroy($ids);
  }

  public function write($data)
  {
    $keyName = $this->modelOf()->getKeyName();
    return \DB::transaction(function () use ($data, $keyName) {
      $data = $this->beforeWrite($data);
      $model = $this->modelOf()->updateOrCreate([
        $keyName => $data[$keyName] ?? null
      ], $data);

      $this->afterWrite($model, $data);

      return $model;
    });
  }

  protected function beforeWrite($data)
  {
    return $data;
  }

  protected function afterWrite($model, $data)
  {

  }

  public static function make(...$args)
  {
    return new static(...$args);
  }

  public function writePartial($id, $data)
  {
    return \DB::transaction(function () use ($data, $id) {
      $model = $this->modelOf()->findOrFail($id);
      $data = $this->beforeWrite($data);
      $model->update($data);

      $this->afterWrite($model, $data);

      return $model;
    });


  }

  public function like($id)
  {
    $model = $this->modelOf()->find($id);
    if ($model) {
      return $model->toggleLike(\Auth::id());
    }
    return false;
  }

  public function show($id)
  {
    return $this->modelOf()->findOrFail($id);
  }

  protected function withFilters(Builder $builder, $filters, $others = [])
  {
    $filters && $this->applyFilters($builder, $filters, $others);
  }

  public function applyFilters($builder, $filters, $others = [], \Closure $then = null)
  {
    if (is_array($filters)) {
      $model = $builder->getModel();
      $searchables = $model->getFillable();

      $searchables = array_merge($others, $searchables);

      $table = $model->getTable();
      $builder->where(function ($query) use ($filters, $searchables, $table, $then) {
        foreach ($filters as $key => $value) {
          list($key, $op) = $this->parseFilterKey($key);
          if ($op == 'like') {
            $value = \DB::raw("'%{$value}%'");
          }
          if (in_array($key, $searchables)) {
            /** @var Builder $query */
            $column = $key;
            if (strpos($key, '.') === false) {
              $column = $table . '.' . $key;
            }
            $customOps = $this->customOps();
            if (array_key_exists($op, $customOps)) {
              $customOps[$op]($query, $column, $value);
            } else {
              $query->where($column, $op, $value);
            }
          } else {
            $then && $then($query, $key, $value, $op);
          }
        }
      });
    }
  }

  protected function customOps()
  {
    return [
      'lp' => function ($query, $column, $value) {
        $query->where($column, "like", "{$value}%");
      }
    ];
  }

  public function resolveSort($model, $order, $builder, \Closure $closure = null)
  {
    $orderArr = explode('|', $order, 2);
    $table = $model->getTable();
    $key = $model->getKeyName();
    $by = Arr::get($orderArr, 0);
    $direction = Arr::get($orderArr, 1);
    if (!Str::contains($by, '.') && $by) {
      $by = "{$table}.{$by}";
    }

    list($o, $d) = [$by ?: $table . '.' . $key, $direction ?: 'desc'];
    if ($closure) {
      $closure($builder);
    }
    if ($by) {
      $builder->getQuery()->orders = [];
      $builder->orderBy($o, $d);
    } else if (!$builder->getQuery()->orders) {
      $builder->orderBy($o, $d);
    }

    return $builder;
  }
}
