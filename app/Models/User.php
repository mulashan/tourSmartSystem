<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'branch_id',
        'privilege_id',
        'photo',
        'email',
        'password',
    ];

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
            'password' => 'hashed',
        ];
    }

    public function subdepartments()
    {
        return $this->belongsToMany(Subdepartment::class, 'tbl_user_subdepartments', 'user_id', 'subdepartment_id', 'id', 'Subdepartment_ID');
    }

    public function approvalPermissions()
    {
        return $this->belongsToMany(ApprovalPermission::class, 'user_approval_permissions', 'user_id', 'approval_permission_id');
    }

    public function hasApprovalPermission(string $key): bool
    {
        return $this->approvalPermissions()->where('key', $key)->exists();
    }

    /* addition */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'Branch_ID');
    }

    public function privilege()
    {
        return $this->belongsTo(UserPrivilege::class, 'privilege_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branches', 'user_id', 'branch_id', 'id', 'Branch_ID');
    }

    public function menuPermissions()
    {
        return $this->hasMany(UserMenuPermission::class);
    }
}
