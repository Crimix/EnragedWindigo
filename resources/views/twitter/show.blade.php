@extends('layouts.noapp')

@section('content')
<div class="row">
  <div class="col-sm-6">
    {!! $analysisChartScatter->render() !!}
  </div>
  <div class="col-sm-6">
    {!! $analysisChartBar->render() !!}
  </div>
</div>
@endsection