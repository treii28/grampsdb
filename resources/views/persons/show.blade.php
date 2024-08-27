@extends('layouts.default')
@section('content')

    <div class="container">
        <h1>Show</h1>

        <div class="row">
            {{ $person->prefix }}
            {{ $person->firstName }}
            @if(!empty($person->nickName))
                "{{ $person->nickName }}"
            @endif
            {{ $person->middleName }}
            {{ $person->lastName }}
            {{ $person->suffix }}
            ({{ $person->gender }})
        </div>
        @if(count($relations['parents']) > 0)
            <div class="well">
                <h3>Parents</h3>
                @foreach($relations['parents'] as $parent)
                    <div class="row">
                        <a href="{{ url( sprintf("/woodgen/persons/%d", $parent->id) ) }}">{{ $parent->getFullName() }}</a>
                    </div>
                @endforeach
            </div>
        @endif
        @if(count($relations['stepparents']) > 0)
            <div class="well">
                <h3>Step Parents</h3>
                @foreach($relations['stepparents'] as $stepparent)
                    <div class="row">
                        <a href="{{ url( sprintf("/woodgen/persons/%d", $stepparent->id) ) }}">{{ $stepparent->getFullName() }}</a>
                    </div>
                @endforeach
            </div>
        @endif
        @if(count($relations['spouses']) > 0)
        <div class="well">
            <h3>Spouses</h3>
            @foreach($relations['spouses'] as $spouse)
                <div class="row">
                    <a href="{{ url( sprintf("/woodgen/persons/%d", $spouse->id) ) }}">{{ $spouse->getFullName() }}</a>
                </div>
            @endforeach
        </div>
        @endif
        @if(count($relations['siblings']) > 0)
            <div class="well">
                <h3>Siblings</h3>
                @foreach($relations['siblings'] as $sibling)
                    <div class="row">
                        <a href="{{ url( sprintf("/woodgen/persons/%d", $sibling->id) ) }}">{{ $sibling->getFullName() }}</a>
                    </div>
                @endforeach
            </div>
        @endif
        @if(count($relations['children']) > 0)
            <div class="well">
                <h3>Children</h3>
                @foreach($relations['children'] as $child)
                    <div class="row">
                        <a href="{{ url( sprintf("/woodgen/persons/%d", $child->id) ) }}">{{ $child->getFullName() }}</a>
                    </div>
                @endforeach
            </div>
        @endif
        @if(count($relations['stepchildren']) > 0)
            <div class="well">
                <h3>Step Children</h3>
                @foreach($relations['stepchildren'] as $stepchild)
                    <div class="row">
                        <a href="{{ url( sprintf("/woodgen/persons/%d", $stepchild->id) ) }}">{{ $stepchild->getFullName() }}</a>
                    </div>
                @endforeach
            </div>
        @endif

        @if(count($links) > 0)
            <div class="well">
                <h3>Links</h3>
                @foreach($links as $link)
                    <div class="row">
                        <a href="{{ $link->url }}">{{ $link->text }}</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection