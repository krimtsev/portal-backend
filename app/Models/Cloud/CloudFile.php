<?php

namespace App\Models\Cloud;

use Illuminate\Database\Eloquent\Model;

class CloudFile extends Model
{
    protected $table = 'cloud_files';

    protected $fillable = [
        'title',
        'name',
        'origin',
        'path',
        'type',
        'downloads',
        'ext',
        'cloud_folders_id',
    ];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];
}
