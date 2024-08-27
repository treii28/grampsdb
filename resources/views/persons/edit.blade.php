@extends('layouts.default')
@section('content')


    <div class="container">
        {!! Form::model($person, ['method' => "PUT", 'route' => ['persons.update', $person->id]])  !!}

        <div class="well">
            <div class="row">
                <h1>Edit</h1>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('firstName', "First Name", ['class' => "control-label"]) !!}
                        {!! Form::text('firstName', old('firstName'), ['class' => "form-control"]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('middleName', "Middle Name", ['class' => "control-label"]) !!}
                        {!! Form::text('middleName', old('middleName'), ['class' => "form-control"]) !!}
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('lastName', "Last Name", ['class' => "control-label"]) !!}
                        {!! Form::text('lastName', old('lastName'), ['class' => "form-control"]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('prefix', "prefix (optional)", ['class' => "control-label"]) !!}
                        {!! Form::text('prefix', old('prefix'), ['class' => "form-control"]) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('suffix', "suffix (optional)", ['class' => "control-label"]) !!}
                        {!! Form::text('suffix', old('suffix'), ['class' => "form-control"]) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('nickName', "Nickname (optional)", ['class' => "control-label"]) !!}
                        {!! Form::text('nickName', old('nickname'), ['class' => "form-control"]) !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-select">
                        {!! Form::label('gender', "Gender", ['class' => "control-label"]) !!} <br />
                        {!! Form::select('gender', ['Male'=>"Male",'Female'=>"Female",'Other'=>"Other"], old('gender'), ['class' => "form-control"]) !!}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group">
                    {!! Form::submit('Update Person', ['class' => "btn btn-primary"]) !!}
                </div>
            </div>
        </div>

        {!! Form::close() !!}
    </div>

@endsection