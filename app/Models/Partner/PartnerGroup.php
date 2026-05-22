<?php

namespace App\Models\Partner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnerGroup extends Model
{
    protected $table = 'partner_groups';

    protected $fillable = [
        'title',
    ];

    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class, 'group_id');
    }
}
