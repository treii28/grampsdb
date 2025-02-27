<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Treii28\Grampsdb\Grampsdb;

class person_links extends Pivot
{
    //protected $connection = 'woodgen';

    /**
     * default name to use for the config values database table
     */
    const SHORTNAME = "person_link";

    /**
     * @var string $table
     */
    protected $table = Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's';

    public static function getTableName() { return Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's'; }

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();
        $table->timestamps();

        $table->foreignId('link_id')->references('id')->on(Link::getTableName());
        $table->foreignId('person_id')->references('id')->on(Person::getTableName());
    }

}
