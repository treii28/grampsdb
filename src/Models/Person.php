<?php

namespace Treii28\Grampsdb\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Treii28\Grampsdb\Grampsdb;
use Treii28\Grampsdb\GrampsdbHelper;

class Person extends Model
{
    //use HasFactory;

    //protected $connection = 'woodgen';

    /**
     * @var stdClass|null $grampsObj
     */
    private $grampsObj;

    /**
     * default name to use for the config values database table
     */
    const SHORTNAME = "person";

    /**
     * for laravel to specify configurable values
     */
    const FILLABLE_COLUMNS = [
        "prefix",
        "firstName",
        "middleName",
        "lastName",
        "nickName",
        "suffix",
        "gender"
    ];

    /**
     * @var string $table
     */
    protected $table = Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's';

    public static function getTableName() { return Grampsdb::TABLE_PREFIX . self::SHORTNAME . 's'; }

    public static function getTableBlueprint(\Illuminate\Database\Schema\Blueprint $table)
    {
        $table->id();

        $table->string("code", 4)->unique();
        $table->string("prefix", 4)->nullable(true);
        $table->string("gramps_id");
        $table->string("firstName");
        $table->string("middleName")->nullable(true);
        $table->string("lastName");
        $table->string("birthName")->nullable(true);
        $table->string("nickName")->nullable(true);
        $table->string("suffix")->nullable(true);
        $table->enum("gender", ['Male', 'Female', 'Other']);

        $table->timestamps();
    }

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

    /**
     * @param string $gramps_id
     * @return Person|null
     */
    public static function findByGrampsId($gramps_id=null)
    {
        if(!empty($gramps_id))
            return Person::where('gramps_id', $gramps_id)->first();
        else
            return null;
    }

    /**
     * @param $gramps_id
     * @return stdClass|null
     */
    public function getGrampsObject($gramps_id=null)
    {
        if(!empty($gramps_id))
            return GrampsdbHelper::getPersonById($gramps_id);
        elseif($this->grampsObj instanceof stdClass)
            return $this->grampsObj;
        elseif(!empty($this->gramps_id)) {
            $this->grampsObj = GrampsdbHelper::getPersonById($this->gramps_id);
            return $this->grampsObj;
        } else
            return null;
    }

    public function getFullName() : string
    {
        return implode(" ", [$this->prefix, $this->firstName, $this->middleName, $this->lastName, $this->suffix]);
    }

    /**
     * @return [Person]
     */
    public function getSpouses() : array
    {
        $spouses = [];
        foreach($this->primaryRelations as $r)
            if($r->Type == 'spouse') {
                $sObj = Person::find($r->secondary_id);
                if($sObj instanceof Person)
                    array_push($spouses, $sObj);
            }

        foreach($this->secondaryRelations as $s)
            if($s->Type == 'spouse') {
                $pObj = Person::find($r->primary_id);
                if($pObj instanceof Person)
                    array_push($spouses, $pObj);
            }

        return $spouses;
    }

    /**
     * @return [Person]
     */
    public function getChildren() : array
    {
        $children = [];
        foreach($this->primaryRelations as $r) {
            if($r->Type == 'child') {
                $sObj = Person::find($r->secondary_id);
                if($sObj instanceof Person)
                    array_push($children, $sObj);
            }
        }
        return $children;
    }

    /**
     * @return [Person]
     */
    public function getParents() : array
    {
        $parents = [];
        foreach($this->secondaryRelations as $r) {
            if($r->Type == 'child') {
                $pObj = Person::find($r->primary_id);
                if($pObj instanceof Person)
                    array_push($parents, $pObj);
            }
        }
        return $parents;
    }

    public function getSiblings()
    {
        $siblings = [];
        foreach($this->getParents() as $parent)
            foreach($parent->getChildren() as $sibling) {
                $sId = $sibling->id;
                if(($sId != $this->id) && (!array_key_exists($sId, $siblings)))
                    $siblings[$sId] = $sibling;
            }

        return $siblings;
    }
    public static function getByCode(string $code) : Person
    {
        $pCode = Person::where('code', $code)->first();
        if($pCode instanceof Person)
            return $pCode;
        else
            throw new \Exception("code not found: " . $code);
    }

    /**
     * @return HasMany
     */
    public function primaryRelations() : HasMany
    {
        return $this->hasMany(Relation::class, 'primary_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function secondaryRelations() : HasMany
    {
        return $this->hasMany(Relation::class, 'secondary_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function links() : BelongsToMany
    {
        return $this->belongsToMany(Link::class, person_links::getTableName());
    }
}
