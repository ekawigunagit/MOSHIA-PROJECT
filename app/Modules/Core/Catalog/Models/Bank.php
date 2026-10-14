<?php
namespace App\Modules\Core\Catalog\Models;
use Illuminate\Database\Eloquent\Model;
class Bank extends Model {
    protected $table = 'core_banks';
    protected $fillable = ['name'];
}
