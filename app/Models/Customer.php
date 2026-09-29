<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Traits\Uuid;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\CustomerResetPassword;

class Customer extends Authenticatable implements CanResetPassword
{
    use Uuid;
    use HasFactory;
    use Notifiable, CanResetPasswordTrait;
    protected $table = 'customers';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
'first_name',
'last_name',
'email',
'password',
'phone',
'birthday',
'postcode',
'sub_district',
'district',
'city',
'province',
'address',
'onboarding_channel',
'is_default',
        'created_by',
        'updated_by',
        'restored_by',
        'restored_at',
        'deleted_by',
        'deleted_at',
        'oauth_provider',
        'oauth_id',
        'oauth_token',
        'oauth_refresh_token'
    ];

    protected $hidden = [
        'password',
    ];

    public function salesTransactions()
    {
        return $this->hasMany(SalesTransaction::class, 'customer_id', 'id');
    }

    public function sendPasswordResetNotification($token)
    {
        $url = url(route('customer.password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));

        $this->notify(new CustomerResetPassword($token, $this->email));
    } 
}