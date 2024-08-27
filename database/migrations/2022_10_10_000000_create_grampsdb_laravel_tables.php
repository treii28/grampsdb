<?php

use \Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Treii28\Grampsdb\Models\Person;
use Treii28\Grampsdb\Models\Event;
use Treii28\Grampsdb\Models\Link;
use Treii28\Grampsdb\Models\Relation;
use Treii28\Grampsdb\Models\Unpicklecache;
use Treii28\Grampsdb\Models\person_links;

class CreateGrampsdbLaravelTables extends Migration
{
    //protected $connection = 'woodgen';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Person::getTableName(),        function(Blueprint $table) {Person::getTableBlueprint($table);});
        Schema::create(Event::getTableName(),         function(Blueprint $table) {Event::getTableBlueprint($table);});
        Schema::create(Link::getTableName(),          function(Blueprint $table) {Link::getTableBlueprint($table);});
        Schema::create(Relation::getTableName(),      function(Blueprint $table) {Relation::getTableBlueprint($table);});
        Schema::create(Unpicklecache::getTableName(), function(Blueprint $table) {Unpicklecache::getTableBlueprint($table);});
        Schema::create(person_links::getTableName(),  function(Blueprint $table) {person_links::getTableBlueprint($table);});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(person_links::getTableName());
        Schema::dropIfExists(Unpicklecache::getTableName());
        Schema::dropIfExists(Relation::getTableName());
        Schema::dropIfExists(Link::getTableName());
        Schema::dropIfExists(Event::getTableName());
        Schema::dropIfExists(Person::getTableName());
    }
}