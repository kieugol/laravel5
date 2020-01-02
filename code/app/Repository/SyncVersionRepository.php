<?php

namespace App\Repository;

use App\Model\SyncVersion;

class SyncVersionRepository extends BaseRepository {

    public function __construct(SyncVersion $model) {
        parent::__construct($model);
    }


    public function getListSyncVersionByType($type) {
        return $this->getModel()
            ->where('type', $type)
            ->orderBy("id", "desc")->first();
    }

}
