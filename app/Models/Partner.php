<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = "partners";

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'pay_end' => 'datetime:Y-m-d H:i:s',
    ];

    protected $fillable = [
        "organization",
        "inn",
        "name",
        "contract_number",
        "email",
        "telnums",
        "address",
        "start_at",
        "yclients_id",
        "disabled"
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
