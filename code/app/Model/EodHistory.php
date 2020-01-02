<?php

namespace App\Model;

use App\Model\UserAdmin;

class EodHistory extends BaseModel
{
    protected $table = 'eod_history';

    //public $timestamps = false;
    protected $fillable = [
        'admin_user_id',
        'payment_data',
        'path',
        'destination_ftp',
        'file_name',
        'is_sent_ftp',
        'start_date',
        'end_date'
    ];

    public function admin_users()
    {
        return $this->hasOne(UserAdmin::class, "id", "admin_user_id");
    }
}
