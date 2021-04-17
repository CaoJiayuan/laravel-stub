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
    $model = $this->modelOf()->findOrFail($id);

    $model->update($data);
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
