<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerReportSetting extends Model
{
    protected $fillable = [
        'partner_id',
        'lost_clients_days',
        'returned_clients_days',
        'new_clients_days',
    ];

    protected $casts = [
        'lost_clients_days'     => 'integer',
        'returned_clients_days' => 'integer',
        'new_clients_days'      => 'integer',
    ];

    /**
     * Партнер, которому принадлежат настройки
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
}
