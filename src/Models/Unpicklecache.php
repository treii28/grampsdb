<?php

namespace Treii28\Grampsdb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unpicklecache extends Model
{
    protected $connection = 'grampsdb';

    //use HasFactory;
    protected $fillable = [
        'dataType', 'gramps_id',
        //'sha1', 'md5',
        // 'raw',
        'mapped'
    ];
}
