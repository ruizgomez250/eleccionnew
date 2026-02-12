<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'autorizar_modif',
        'sistema',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    // Dentro del modelo Usuario (o User)
    public function archivosDocumentos()
    {
        return $this->hasMany(ArchivosDocumento::class, 'id_usuario');
    }
    public function recorridoDocs()
    {
        return $this->hasMany(RecorridoDoc::class, 'id_usuario');
    }
    public function mesaEntrada()
    {
        return $this->hasMany(MesaEntrada::class, 'id_usuario');
    }
    public function sistemaRelacion()
    {
        return $this->belongsTo(Sistema::class, 'sistema');
    }
}
