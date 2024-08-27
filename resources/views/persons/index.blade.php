@extends('layouts.default')
@section('content')

    <div class="container">
        <div class="row"><h1>Persons</h1></div>
        <div class="well">
            @if(count($persons))
                @foreach($persons as $person)
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="{{ url( sprintf("/woodgen/persons/%d", $person->id) ) }}">{{ $person->getFullName() }}</a>
                        </div>
                        <div class="col-sm-4"></div>
                        <div class="col-sm-1"><a href="{{ url( sprintf("/woodgen/persons/%d/edit", $person->id) ) }}"><i class="bi bi-pen"></i></a></div>
                        <div class="col-sm-1"><a href="{{ url( sprintf("/woodgen/persons/%d/delete", $person->id) ) }}"><i class="bi bi-trash"></i></a></div>
                    </div>
                @endforeach
            @else
                <div class="row"><em>(no persons in database)</em></div>
            @endif
        </div>
        <div class="row">
            <a href="{{ url('/woodgen/persons/create') }}" class="btn btn-primary">Create</a>
        </div>
    </div>

@endsection
