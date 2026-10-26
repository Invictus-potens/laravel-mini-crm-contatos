<?php

namespace App\Infraestrutura\Persistencia;

use App\Dominio\Contato\ObjetosDeValor\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactModel extends Model
{
    use SoftDeletes;

    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'score',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'status' => Status::class,
            'processed_at' => 'immutable_datetime',
        ];
    }
}