<?php
namespace App\Modules\Core\Media\Models;
use Illuminate\Database\Eloquent\Model;
class Media extends Model {
    protected $table = 'core_media';
    protected $guarded = ['id'];
}
