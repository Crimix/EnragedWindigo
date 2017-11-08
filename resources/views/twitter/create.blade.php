@extends('layouts.app')

@section('content')
<form method="post" action="{{ route('twitter.store') }}" class="form">
{{ csrf_field() }}
<div class="container">
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="panel panel-default">
        <div class="panel-body">
          Please enter the username of the Twitter user you want to check, as well
          as the email-address on which you'd like to be notified when the processing
          is completed.
        </div>
      </div>
      
      <div class="form-group{{ $errors->has('twitterUser') ? ' has-error' : '' }}">
        <label for="twitterUser" class="control-label">Username</label>
        <input type="text" id="twitterUser" name="twitterUser" class="form-control" required>

        @if ($errors->has('twitterUser'))
          <span class="help-block">
            <strong>{{ $errors->first('twitterUser') }}</strong>
          </span>
        @endif
      </div>

      <div class="form-group{{ $errors->has('userEmail') ? ' has-error' : '' }}">
        <label for="userEmail" class="control-label">Email-address</label>
        <input type="text" id="userEmail" name="userEmail" class="form-control" required>

        @if ($errors->has('userEmail'))
          <span class="help-block">
            <strong>{{ $errors->first('userEmail') }}</strong>
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