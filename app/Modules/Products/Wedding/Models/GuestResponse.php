<?php
namespace App\Modules\Products\Wedding\Models;
use Illuminate\Database\Eloquent\Model;
class GuestResponse extends Model {
    protected $table = 'wedding_responses';
    protected $guarded = ['id'];
    protected function casts(): array { return ['approved' => 'boolean']; }
}
