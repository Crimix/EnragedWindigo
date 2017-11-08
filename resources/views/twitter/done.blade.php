@extends('layouts.app')

@section('content')
<h1>Done!</h1>
<pre>
{{ print_r($twitterRequest, true) }}
</pre>
@endsection