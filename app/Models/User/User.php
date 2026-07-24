<?php

namespace App\Models\User;

use App\Enums\User\UserRole;
use App\Models\Department\Department;
use App\Models\Partner\Partner;
use App\Observers\User\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[ObservedBy([UserObserver::class])]
final class User extends Authenticatable
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
        'time_zone_name',
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

    protected function userName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name ?: $this->login,
        );
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isSysAdmin(): bool
    {
        return $this->role === UserRole::Sysadmin->value;
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

    public function scopeActiveInDepartment($query, int $departmentId)
    {
        return $query->where('disabled', false)
            ->whereNotNull('email')
            ->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId);
            });
    }

    /**
     * Получение активных пользователей с выборкой динамических полей
     *
     * @param  Builder  $query
     * @param  array  $fields  - какие поля вернуть
     */
    public function scopeActiveWhere($query, array $fields = ['id', 'name']): Builder
    {
        $query->where('disabled', 0);

        $selectFields = array_filter(
            $fields,
            fn ($field) => in_array($field, $this->fillable) || $field === 'id'
        );

        return $query->select($selectFields);
    }
}
