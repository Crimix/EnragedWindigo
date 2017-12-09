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
<div class="row">
  <div class="col-sm-6">
    {!! $miChartScatter->render() !!}
  </div>
  <div class="col-sm-6">
    {!! $miChartBar->render() !!}
  </div>
</div>
<div class="row">
  <div class="col-sm-6">
    {!! $sentimentChartBar->render() !!}
  </div>
  <div class="col-sm-6">
    {!! $mediaChartBar->render() !!}
  </div>
</div>
@endsection