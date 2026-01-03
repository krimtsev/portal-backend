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

    /*
    public function folders(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CloudFolder::class, 'id', 'cloud_folders_id');
    }

    public function folder(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CloudFolder::class, 'id', 'cloud_folders_id');
    }
    */
}
