<?php

namespace App\Models\Cloud;

use App\Models\Cloud\Traits\HasBreadcrumbs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CloudFolder extends Model
{
    use HasBreadcrumbs;

    public const CACHE_TTL = 7200; // 2 часа
    public const CACHE_TAG = "cloud";

    protected $table = 'cloud_folders';

    protected $fillable = [
        'name',
        'slug',
        'folder',
        'category_id',
    ];

    protected $casts = [
        'created_at'  => 'date:Y-m-d',
    ];

    /**
     * Дочерние папки
     */
    public function children(): HasMany
    {
        return $this->hasMany(CloudFolder::class, 'category_id');
    }

    /**
     * Родительская папка
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CloudFolder::class, 'category_id');
    }


    /**
     * Рекурсивная загрузка всех детей
     */
    public function childrenRecursive(): Builder|HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    public function withFilesRecursive(): HasMany
    {
        return $this->children()->with(['withFilesRecursive', 'files' => function($q) {
            $q->select('id', 'title', 'ext', 'cloud_folders_id');
        }]);
    }

    public function files(): HasMany
    {
        return $this->hasMany(CloudFile::class, 'cloud_folders_id')
            ->select('id', 'title', 'ext', 'downloads', 'cloud_folders_id', 'name');
    }

    /**
     * Получить все ID текущей и вложенных папок
     */
    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }
}
