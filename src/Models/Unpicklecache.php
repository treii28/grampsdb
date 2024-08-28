<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;
use Treii28\Grampsdb\Grampsdb;

//use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unpicklecache extends Model
{
    //protected $connection = 'woodgen';

    //use HasFactory;

    /**
     * default name to use for the config values database table
     */
    const SHORTNAME = "unpicklecache";

    /**
     * for laravel to specify configurable values
     */
    const FILLABLE_COLUMNS = [
        'dataType',
        'gramps_id',
        //'sha1', 'md5',
        // 'raw',
        'mapped'
    ];

    /**
     * @var string $table
     */
    protected $table = Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's';

    public static function getTableName() { return Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's'; }

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();
        $table->string("dataType");
        $table->string("gramps_id");
        $table->string("hash");
        //$table->binary("raw");
        $table->binary("json");

        $table->timestamps();
    }

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

}
