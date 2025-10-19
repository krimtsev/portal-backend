<?php

namespace App\Models\Cloud;

use App\Models\Cloud\Traits\HasBreadcrumbs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CloudFolder extends Model
{
    use HasFactory, HasBreadcrumbs;

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
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CloudFolder::class, 'category_id');
    }

    /**
     * Родительская папка
     */
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CloudFolder::class, 'category_id');
    }


    // Рекурсивная загрузка всех детей
    public function allChildrenRecursive()
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
