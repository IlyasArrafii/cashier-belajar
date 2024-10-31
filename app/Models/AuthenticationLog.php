<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationLog extends Model
{

    //use Prunable;
    use MassPrunable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'authentication_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'authenticatable_type',
        'authenticatable_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'location' => 'array',
        'login_successful' => 'boolean',
        'cleared_by_user' => 'boolean',
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return static::query()->where('login_at', '<=', now());
    }
}
