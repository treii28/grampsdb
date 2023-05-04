<?php

namespace Treii28\Grampsdb\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    //use HasFactory;

    protected $connection = 'grampsdb';

    /**
     * default name to use for the config values database table
     */
    const DEFAULT_DB_TABLE_NAME = "persons";

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
    protected $table = self::DEFAULT_DB_TABLE_NAME;

    /**
     * list of user/class modifiable db table columns
     *
     * @var string[] $fillable
     */
    protected $fillable = self::FILLABLE_COLUMNS;

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
                $sObj = self::find($r->secondary_id);
                if($sObj instanceof Person)
                    array_push($spouses, $sObj);
            }

        foreach($this->secondaryRelations as $s)
            if($s->Type == 'spouse') {
                $pObj = self::find($r->primary_id);
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
                $sObj = self::find($r->secondary_id);
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
                $pObj = self::find($r->primary_id);
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
        $pCode = static::where('code', $code)->first();
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
        return $this->belongsToMany(Link::class);
    }
}
