<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartnerTelnum extends Model
{
    use HasFactory;

    protected $table = 'partner_telnums';

    protected $fillable = [
        'partner_id',
        'name',
        'number',
    ];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}
