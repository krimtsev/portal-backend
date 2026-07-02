<?php

declare(strict_types=1);

namespace App\Models\Yclient;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YcRecordDocument extends Model
{
    /**
     * @var string
     */
    protected $table = 'yc_record_documents';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'record_id',
        'document_id',
        'company_id',
        'type_id',
        'type_title',
        'storage_id',
        'user_id',
        'date_created',
        'visit_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_id'  => 'integer',
            'company_id'   => 'integer',
            'type_id'      => 'integer',
            'type_title'   => 'string',
            'storage_id'   => 'integer',
            'user_id'      => 'integer',
            'date_created' => 'datetime',
            'visit_id'     => 'integer',
            'record_id'    => 'integer',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(YcRecord::class, 'record_id', 'record_id');
    }
}
