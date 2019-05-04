<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    protected $fillable = [
        'smakeId',
        'smakeFileName',
        'fileName',
        'originalName',
        'mimeType',
        'fileSize',
        'path',
        'downloadUrl'
    ];
}
