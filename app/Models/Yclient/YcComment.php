<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcComment extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_comments';

    protected $primaryKey = 'comment_id';

    public $incrementing = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'comment_id',
        'company_id',
        'salon_id',
        'staff_id',
        'rating',
        'type',
        'date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'comment_id' => 'integer',
            'company_id' => 'integer',
            'salon_id'   => 'integer',
            'staff_id'   => 'integer',
            'rating'     => 'integer',
            'type'       => 'integer',
            'date'       => 'datetime',
        ];
    }
}
