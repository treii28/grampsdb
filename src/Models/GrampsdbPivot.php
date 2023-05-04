<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

abstract class GrampsdbPivot extends Pivot
{
    protected $connection = 'grampsdb';
}