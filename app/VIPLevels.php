<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VIPLevels extends Model
{

    protected $connection = 'mongodb';
    protected $collection = 'viplevels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '_id',
        'level',
        'icon',
        'level_name',
        'start',
        'rake_percent',
        'promocode_bonus',
        'faucet_bonus',
        'fs_bonus',
        'challenges_bonus'
    ];

}

