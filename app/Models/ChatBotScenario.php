<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChatBotScenario extends Model {
    protected $fillable=['name','keywords','response','priority','is_active','handoff_to_staff','matched_count'];
    protected $casts=['keywords'=>'array','priority'=>'integer','is_active'=>'boolean','handoff_to_staff'=>'boolean','matched_count'=>'integer'];
}
