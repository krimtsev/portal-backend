<?php

namespace App\Models\User;

use App\Models\Partner\Partner;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\User\UserObserver;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
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
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
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
            UserDepartment::class,
            'user_departments',
            'user_id',
            'department_slug',
            'id',
            'department_slug'
        )->using(UserDepartment::class);
    }

    /**
     * Scope для получения уникальных пользователей по списку отделов
     */
    public function scopeInDepartments($query, array $slugs)
    {
        return $query->whereHas('departments', function($q) use ($slugs) {
            $q->whereIn('department_slug', $slugs);
        });
    }
}
