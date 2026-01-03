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

    const FOLDER = 'cloud';

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


    // Рекурсивная загрузка всех детей
    public function allChildrenRecursive(): Builder|HasMany
    {
        return $this->children()->with('allChildrenRecursive');
    }

    // Получить все ID текущей и вложенных папок
    public function getAllChildrenIds(): array
    {
        $ids = [$this->id];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }
}
