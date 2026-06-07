<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XrayImage extends Model
{
protected $fillable = ['patient_id', 'file_path', 'type', 'notes'];
}
