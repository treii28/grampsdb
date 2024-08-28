<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;
use Treii28\Grampsdb\Grampsdb;

//use Illuminate\Database\Eloquent\Factories\HasFactory;

class Link extends Model
{
    //use HasFactory;

    //protected $connection = 'woodgen';

    /**
     * default name to use for the config values database table
     */
    const SHORTNAME = "link";

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
    protected $table = Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's';

    public static function getTableName() { return Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's'; }

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();

        $table->string("url");
        $table->string("text");

        $table->timestamps();
    }

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

    public function persons()
    {
        return $this->hasManyThrough(Person::class, person_links::class, 'link_id', 'person_id');
    }
}
