@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-sm-6">
    {!! $analysisChartScatter->render() !!}
  </div>
  <div class="col-sm-6"></div>
</div>
@endsection