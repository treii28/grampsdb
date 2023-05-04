person show
<!-- // DEBUG
bucket: {{ $aws['bucket'] }}
region: {{ $aws['region']}}
path: {{ $aws['path'] }}
-->
{{ $person->given_name }} {{ $person->surname }}
<ul>
    @foreach ($person->media as $mId => $mData)
        <li><a href="{{ $mData->url }}">{{ $mId }}</a></li>
    @endforeach
</ul>
