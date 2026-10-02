<?php

namespace App\Modules\Core\Tenancy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $table = 'core_tenants';

    protected $fillable = ['name', 'owner_id'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'core_tenant_user')->withTimestamps();
    }
}
