<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionProcedure extends Model
{
protected $fillable = ['session_id', 'service_id', 'price_charged'];

public function service() { return $this->belongsTo(ServicePriceList::class, 'service_id'); }
}
