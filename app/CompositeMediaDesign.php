<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompositeMediaDesign extends Model
{
    protected $fillable = [
        'designName',
        'baseColor',
        'designId',
        'smakeId',
        'fileName',
        'fileFolder',
        'fileSize',
        'smakeFileName',
        'smakeDownloadUrl',
        'width_px',
        'height_px'

    ];
}
