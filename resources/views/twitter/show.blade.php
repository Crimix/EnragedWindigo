@extends('layouts.noapp')

@section('content')
<div class="row">
  <div class="col-sm-8 col-sm-offset-2 text-center">
    <h1>{{ $twitterName }}</h1>
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    //
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    {!! $analysisChartScatter->render() !!}
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    {!! $analysisChartBar->render() !!}
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    {!! $miChartBar->render() !!}
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    {!! $sentimentChartBar->render() !!}
  </div>
</div>
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    {!! $mediaChartBar->render() !!}
  </div>
</div>
@endsection