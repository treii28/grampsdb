<?php

namespace Treii28\Grampsdb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    //use HasFactory;
    //use HasFactory;
    /**
     * default name to use for the config values database table
     */
    const DEFAULT_DB_TABLE_NAME = "links";

    /**
     * for laravel to specify configurable values
     */
    const FILLABLE_COLUMNS = [
        "person_id",
        "url",
        "text"
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

    public function persons()
    {
        return $this->hasMany(Person::class, 'id', 'person_id');
    }
}
