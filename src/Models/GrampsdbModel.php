<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;

abstract class GrampsdbModel extends Model
{
    protected $connection = 'grampsdb';
}