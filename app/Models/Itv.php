<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itv extends Model
{
    use HasFactory;
    protected $table = 'itv';

    protected $fillable = [
        'name', 'number', 'censored', 'status', 'tv_genre_id', 'xmltv_id', 'mc_cmd', 'enable_tv_archive', 'logo', 'tv_archive_duration', 'epg_offset'
    ];
    public function tvGenre()
    {
        return $this->belongsTo(TvGenre::class, 'tv_genre_id');
    }
    public function chLinks()
    {
        return $this->hasMany(ChLinks::class, 'ch_id', 'id');
    }
}
