<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePriceList extends Model
{
    protected $table = 'service_price_list';
protected $fillable = ['branch_id', 'service_name', 'price'];
}
