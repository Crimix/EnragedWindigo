@extends('layouts.app')

@section('content')
<form method="post" action="{{ route('twitter.confirmKey') }}" class="form">
{{ csrf_field() }}
<div class="container">
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="panel panel-default">
        <div class="panel-body">
          The site is running in "out-of-band" mode, so in order to proceed, you need to
          <a href="{{ $authUrl }}" target="_blank">click here to approve the request</a>
          and then enter the provided PIN in the form below in order to proceed.
        </div>
      </div>

      <div class="form-group{{ $errors->has('pin_number') ? ' has-error' : '' }}">
        <label for="pin_number" class="control-label">PIN number</label>
        <input id="pin_number" type="number" name="pin_number" class="form-control" required>

        @if ($errors->has('pin_number'))
          <span class="help-block">
            <strong>{{ $errors->first('pin_number') }}</strong>
          </span>
        @endif
      </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary form-control">Submit</button>
      </div>
    </div>
  </div>
</div>
</form>
@endsection