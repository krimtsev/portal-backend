<?php

namespace App\Models\Cloud;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CloudFile extends Model
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
        'size',
        'checksum',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'date:Y-m-d',
    ];

    protected $appends = ['author'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAuthorAttribute(): ?string
    {
        return $this->user?->name;
    }
}
