<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerTelnum extends Model
{
    protected $table = 'partner_telnums';

    protected $fillable = [
        'partner_id',
        'name',
        'number',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
