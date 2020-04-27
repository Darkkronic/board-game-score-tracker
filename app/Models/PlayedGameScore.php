<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayedGameScore extends Model
{
    protected $fillable = [
        'played_game_id', 'group_user_id', 'score', 'place', ' remarks', 'creator_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function playedGame()
    {
        return $this->belongsTo('App\Models\PlayedGame', 'played_game_id', 'id')->withDefault();
    }

    public function groupUser()
    {
        return $this->belongsTo('App\Models\GroupUser', 'group_user_id', 'id')->withDefault();
    }

}
