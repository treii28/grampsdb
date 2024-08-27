<?php

namespace Treii28\Grampsdb\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Treii28\Grampsdb\Models\Person;
use Treii28\Grampsdb\Models\Relation;

class PersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $persons = Person::all();
        return view('grampsdb-laravel::persons.index', compact('persons'));

        $person = Person::where('firstName', '=', 'about')->where()->firstOrFail();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('grampsdb-laravel::persons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $person = new Person();

        foreach(Person::FILLABLE_COLUMNS as $pName)
            $person->{$pName} = $request->{$pName};

        $person->save();

        return redirect('persons');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $links = [];
        $relations = [
            'siblings' => []
        ];
        foreach(array_unique(array_merge(
            array_values(Relation::PRIMARY_PLURALS),
            array_values(Relation::SECONDARY_PLURALS)
        )) as $key)
            $relations[$key] = [];

        $person = (preg_match('/^I\d+/', $id) ? Person::findByGrampsId($id) : Person::find($id));
        $gramps = (($person instanceof Person) ? $person->getGrampsObject() : null);
        if($person instanceof Person) {
            $links = $person->links;
            foreach($person->primaryRelations as $r) {
                $sObj = Person::find($r->secondary_id);
                if($sObj instanceof Person) {
                    $sId = $sObj->id;
                    $sKey = Relation::PRIMARY_PLURALS[$r->Type];
                    $relations[$sKey][$sId] = $sObj;
                }
            }
            foreach($person->secondaryRelations as $r) {
                $pObj = Person::find($r->primary_id);
                if($pObj instanceof Person) {
                    $pId = $pObj->id;
                    $pKey = Relation::SECONDARY_PLURALS[$r->Type];
                    $relations[$pKey][$pId] = $pObj;
                }
            }
            $relations['siblings'] = $person->getSiblings();
        }

        $foo = count($links);
        return view('grampsdb-laravel::persons.show', compact('person', 'links', 'relations'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $person = Person::find($id);

        return view('grampsdb-laravel::persons.edit', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $person = Person::find($id);

        foreach(Person::FILLABLE_COLUMNS as $pName)
            $person->{$pName} = $request->{$pName};

        $person->save();

        return redirect('persons');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $person = Person::find($id);
        $person->delete();

        return redirect('persons');
    }
}
