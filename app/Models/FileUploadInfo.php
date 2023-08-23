<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUploadInfo extends Model
{
    use HasFactory;

    protected $table = 'file_upload_info';

    protected $fillable = [
        'file_id',
        'file_name',
        'file_path',
        'file_hash',
        'owner_id',
        'total_chunks',
        'uploaded_chunks'
    ];
}
