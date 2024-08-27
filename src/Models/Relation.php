<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;

class Relation extends Model
{
    //use HasFactory;

    //protected $connection = 'woodgen';

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
    const SHORTNAME = "relation";

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
    protected $table = self::SHORTNAME . 's';

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();

        $table->unsignedBigInteger('primary_id');
        $table->unsignedBigInteger('secondary_id');
        $table->enum('Type', ['child','guardian','spouse','stepchild','other']);

        $table->timestamps();

        $table->foreign('primary_id')->references('id')->on(Person::getTableName())
            ->onDelete('cascade');
        $table->foreign('secondary_id')->references('id')->on(Person::getTableName())
            ->onDelete('cascade');
    }

    public static function getTableName() { return self::SHORTNAME . 's'; }

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
