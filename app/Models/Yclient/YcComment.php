<?php

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;

class YcComment extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_comments';

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
            'date' => 'datetime',
        ];
    }
}
