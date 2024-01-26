Person index
<ul>
@foreach ($persons as $pId => $pData)
        <li>{{ $pId }}: <a href="/woodgen/persons/{{ $pId }}">{{ $pData->given_name }} {{ $pData->surname }}</a></li>
@endforeach
</ul>