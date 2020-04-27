<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name' ,'players_min', 'players_max', 'base_game_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    /**
     * Returns the base game of the expansion
     */
    public function baseGame()
    {
        return $this->belongsTo('App\Models\Game', 'base_game_id', 'id');
    }

    /**
     * Returns all expansions of a game
     */
    public function expansions()
    {
        return $this->hasMany('App\Models\Game', 'base_game_id', 'id');
    }

     /**
     * Returns all group games
     */
    public function groupGames()
    {
        return $this->hasMany('App\Models\GroupGame', 'game_id', 'id');
    }

    protected $appends = ['display'];

    /**
     * Returns a name for dropdowns
     */
    public function getDisplayAttribute()
    {
        return $this->full_name;
    }

    //Pivot table connection
    public function playedGameExpansions()
    {
        return $this->belongsToMany('App\Models\PlayedGame', 'expansion_played_game');
    }
}
