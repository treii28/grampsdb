<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;
use Treii28\Grampsdb\Grampsdb;

//use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    //use HasFactory;

    //protected $connection = 'woodgen';

    /**
     * default name to use for the config values database table
     */
    const SHORTNAME = "event";

    /**
     * for laravel to specify configurable values
     */
    const FILLABLE_COLUMNS = [
        "eventName",
        "eventType",
        "eventDate"
    ];

    /**
     * @var string $table
     */
    protected $table = Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's';

    public static function getTableName() { return Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's'; }

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();
        $table->unsignedBigInteger('person_id');
        $table->string("eventName");
        $table->enum("eventType",['birth','marriage','divorce','residence','enlistment','death','other']);
        $table->date("eventDate");

        $table->timestamps();

        $table->foreign('person_id')->references('id')->on(Person::getTableName())
            ->onDelete('cascade');
    }

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

}
