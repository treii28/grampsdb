<?php

namespace Treii28\Grampsdb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    //use HasFactory;

    protected $connection = 'grampsdb';

    const VALID_TYPES = [
        'child',
        'guardian',
        'spouse',
        'other'
    ];

    const PRIMARY_PLURALS = [
        'spouse' => "spouses",
        'child' => "children",
        'stepchild' => "stepchildren",
        'guardian' => "guardians"
    ];
    const SECONDARY_PLURALS = [
        'spouse' => "spouses",
        'child' => "parents",
        'stepchild' => "stepparents",
        'guardian' => "fosterchildren"
    ];
    /**
     * default name to use for the config values database table
     */
    const DEFAULT_DB_TABLE_NAME = "relations";

    /**
     * for laravel to specify configurable values
     */
    const FILLABLE_COLUMNS = [
        "Type",
        "primary_id",
        "secondary_id"
    ];

    /**
     * @var string $table
     */
    protected $table = self::DEFAULT_DB_TABLE_NAME;

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

    public function primaryPersons()
    {
        return $this->belongsTo(Person::class, 'id', 'primary_id');
    }

    public function secondaryPersons()
    {
        return $this->belongsTo(Relation::class, 'id', 'secondary_id');
    }
}
