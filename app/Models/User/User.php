<?php

namespace App\Models\User;

use App\Models\Department\Department;
use App\Models\Partner\Partner;
use App\Observers\User\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login',
        'name',
        'role',
        'partner_id',
        'disabled',
        'avatar',
        'last_activity',
        'email',
        'notes',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'disabled'          => 'boolean',
            'partner_id'        => 'integer',
            'created_at'        => 'datetime',
            'last_activity'     => 'datetime',
            'notes'             => 'string',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function access(): HasOne
    {
        return $this->hasOne(UserAccess::class, 'user_id');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(
            Department::class,
            'user_departments',
            'user_id',
            'department_id'
        )->using(UserDepartment::class);
    }
}
