<?php

namespace App\Models\BankDetail;

use App\Models\Partner\Partner;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BankDetail extends Model
{
    protected $fillable = [
        'user_id',
        'partner_id',
        'entrepreneur_name',
        'inn',
        'checking_account',
        'bank_name',
        'bik',
        'correspondent_account',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
