<?php

namespace App\Repository;
use Illuminate\Support\Facades\DB;

class BaseRepository {

    protected $model;

    /**
     * EloquentRepository constructor.
     * @param $model
     */
    public function __construct($model) {
        $this->model = $model;
    }

    public function getModel() {
        return $this->model;
    }

    public function find($id) {
        return $this->model->find($id);
    }

    public function isEmptyData() {
        return $this->model::count() > 0 ? false : true;
    }

    public function findOrFail($id) {
        return $this->model->findOrFail($id);
    }

    public function all($orderBy = 'DESC', $columnOrderBy = false) {
        if (!$columnOrderBy) {
            return $this->model->orderBy($this->model->getKeyName(), $orderBy)->get();
        } else {
            return $this->model->orderBy($columnOrderBy, $orderBy)->get();
        }
    }

    public function insert($data) {
        return $this->model->insert($data);
    }

    public function create($data) {
        return $this->model->create($data);
    }

    public function update($data, $id) {
        $dataUpdate = array_intersect_key($data, array_flip($this->model->getFillable()));

        return $this->model->where($this->model->getKeyName(), $id)->update($dataUpdate);
    }

    public function save($model, $data) {
        $model->update($data);

        return $model;
    }

    public function destroy($id) {
        return $this->model->destroy($id);
    }

    public function findByAttributes(array $attributes, $orderBy = 'DESC') {
        return $this->model->where($attributes)->orderBy($this->model->getKeyName(), $orderBy)->get();
    }

    public function findByMany(array $ids) {
        $query = $this->model->query();

        return $query->whereIn($this->model->getKeyName(), $ids)->get();
    }

    public function pagination() {
        return $this->model->filterPaginateOrder();
    }

    public function exists(array $condition) {
        $result = $this->model->where($condition)->first();

        return count($result) > 0;
    }

    public function getContant($key) {
        return constant('self::' . $key);
    }

    public function getKeyValue($key, $value) {
        $items = $this->all();
        $data = array();
        foreach ($items as $item) {
            $data[$item->$key] = $item->$value;
        }
        return $data;
    }

    public function truncateData()
    {
        return $this->model->truncate();
    }

    public function insertMultiple(array $data)
    {
        return DB::table($this->model->getTable())->insert($data);
    }

    public function updateWithConditions($dataUpdate, array $conditions = [])
    {
        $model = $this->model;
        if (count($conditions)) {
            $model->where($conditions)->update($dataUpdate);
        } else {
            $model->update($dataUpdate);
        }

        return $model;
    }

    public function getDataTableApi($filters = [], $search = [], $sort = [], $period = [],$limit = 10, $offset = 0)
    {
        $query = $this->model;
        if (sizeof($filters) > 0) {
            foreach ($filters as $field => $key) {
                $query = $query->where($field, $key);
            }
        }

        if (sizeof($search) > 0) {
            foreach ($search as $field => $value) {
                if (!empty($value)) {
                    $query = $query->where($field, 'like', "%" . $value . "%");
                }
            }
        }

        if (!empty($period['fromDate'])) {
            $query = $query->whereRaw("date(created_date) >= '" . $period['fromDate'] . "'");
        }

        if (!empty($period['toDate'])) {
            $query = $query->whereRaw("date(created_date) <= '" . $period['toDate'] . "'");
        }

        $total = $query->count();

        if (sizeof($sort) > 0) {
            foreach ($sort as $field => $type) {
                $query = $query->orderBy($field, $type);
            }
        } else {
            $query = $query->orderBy("id", "DESC");
        }
        $query = $query->offset($offset * $limit);
        $query = $query->limit($limit);

        return ['items' => $query->get(), 'total' => $total, 'limit' => $limit, 'offset' => $offset];
    }
    public function getQueryByAttributes(array $attributes, $orderBy = 'DESC')
    {
        return $this->model->where($attributes)->orderBy($this->model->getKeyName(), $orderBy);
    }

    public function getByPeriod($from_date, $to_date) {
        return $this->model
            ->whereRaw("created_date >= '" . $from_date . "'")
            ->whereRaw("created_date <= '" . $to_date . "'")
            ->get();
    }

}
