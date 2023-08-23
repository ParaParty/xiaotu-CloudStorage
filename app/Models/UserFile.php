<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserFile extends Model
{
    use HasFactory;


    protected $table = 'user_files';

    protected $fillable = [
        'filename',
        'path',
        'owner_id',
        'type',
        'hash',
        'note',
    ];



}
