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
      
      <div class="form-group{{ $errors->has('twitter_username') ? ' has-error' : '' }}">
        <label for="twitter_username" class="control-label">Username</label>
        <input type="text" id="twitter_username" name="twitter_username" class="form-control" required>

        @if ($errors->has('twitter_username'))
          <span class="help-block">
            <strong>{{ $errors->first('twitter_username') }}</strong>
          </span>
        @endif
      </div>

      <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
        <label for="email" class="control-label">Email-address</label>
        <input type="text" id="email" name="email" class="form-control" required>

        @if ($errors->has('email'))
          <span class="help-block">
            <strong>{{ $errors->first('email') }}</strong>
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