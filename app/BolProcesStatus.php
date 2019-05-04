<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BolProcesStatus extends Model
{
    protected $fillable = [
        'process_status_id',
        'entityId',
        'eventType',
        'description',
        'status',
        'errorMessage',
        'link_to_self',
        'method_to_self',
        'createTimestamp'
    ];
}

