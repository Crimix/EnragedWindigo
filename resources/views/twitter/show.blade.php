@extends('layouts.noapp')

@section('barChartDesc')
<p>
  The bars are coloured based on their location so that they turn increasingly blue the further
  to the left they're situated, and similarly with red the further right they are. The colours are
  based on the American flag because reasons.
</p>
<p>
  The user's location is marked by a green bar.
</p>
@endsection

@section('content')
<div class="row">
  <div class="col-sm-8 col-sm-offset-2">
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h1>
          Results for<br>
          <kbd>{{ $twitterName }}</kbd>
        </h1>
      </div>
      <div class="panel-body">
        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Quaerat velit eum
          deleniti vel doloribus harum nihil praesentium, illo unde numquam tenetur
          veritatis reiciendis beatae quo iure corrupti rerum dolorum officiis!
        </p>
        <button data-toggle="collapse" data-target="#stats" class="btn btn-info">
          More information and stats
        </button>

        <div id="stats" class="collapse" style="padding-top: 15px;">
          <p>
            To get these results, {{ $tweetCounts['total'] }} tweets were analysed
            from {{ $userCount }} users, including the target user.
          </p>
          <p>
            Of these tweets, {{ $tweetCounts['user'] }} of them was from the user,
            and the average number of tweets per user (not including the target
            user) is {{ round($tweetCounts['follows']['average'], 2) }}.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-sm-6 col-sm-offset-3">
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h3>
          Bias and Sentiment Distribution<br>
          <small>(Analysis)</small>
        </h3>
      </div>
      <div class="panel-body">
        <p>
          Two-dimensional distribution of the target user and those they follow, showing
          political views together with their sentiment (ie. how positive or negative their
          tweets are on average), based on our algorithmic analysis approach.
          (<a href="" data-toggle="collapse" data-target="#collapseAnalysisScatter"
              onclick="event.preventDefault();">more...</a>)
        </p>
        <div id="collapseAnalysisScatter" class="collapse" style="padding-top: 15px;">
          <p>
            The target user's own dot is marked in green and the remaining dots are divided
            into three groups; left, center, and right.
          </p>
          <p>
            On the sentiment axis (Y-axis) the higher the number the more positive our
            analysis says they are (on average).
          </p>
        </div>
      </div>
      <div class="panel-footer">
        {!! $analysisChartScatter->render() !!}
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h3>
          Bias Distribution<br>
          <small>(Analysis)</small>
        </h3>
      </div>
      <div class="panel-body">
        <p>
          The distribution of political bias (in percent) divided into groups and presented
          as a bar chart. This is based on our algorithmic analysis approach.
          (<a href="" data-toggle="collapse" data-target="#collapseAnalysisBar"
              onclick="event.preventDefault();">more...</a>)
        </p>
        <div id="collapseAnalysisBar" class="collapse" style="padding-top: 15px;">
          @yield('barChartDesc')
        </div>
      </div>
      <div class="panel-footer">
        {!! $analysisChartBar->render() !!}
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h3>
          Bias Distribution<br>
          <small>(MI)</small>
        </h3>
      </div>
      <div class="panel-body">
        <p>
          The distribution of political bias (in percent) divided into groups and presented
          as a bar chart. This is based on our machine learning approach.
          (<a href="" data-toggle="collapse" data-target="#collapseMiBar"
              onclick="event.preventDefault();">more...</a>)
        </p>
        <div id="collapseMiBar" class="collapse" style="padding-top: 15px;">
          @yield('barChartDesc')
        </div>
      </div>
      <div class="panel-footer">
        {!! $miChartBar->render() !!}
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h3>
          Sentiment Distribution
        </h3>
      </div>
      <div class="panel-body">
        <p>
          The distribution of sentiment (in percent) divided into groups and presented
          as a bar chart. This is based on our algorithmic analysis approach.
        </p>
        <p>
          The sentiment is an average estimate of how positive or negative a person's
          tweets are on average.
          (<a href="" data-toggle="collapse" data-target="#collapseSentimentBar"
              onclick="event.preventDefault();">more...</a>)
        </p>
        <div id="collapseSentimentBar" class="collapse" style="padding-top: 15px;">
          @yield('barChartDesc')
        </div>
      </div>
      <div class="panel-footer">
        {!! $sentimentChartBar->render() !!}
      </div>
    </div>
    <div class="panel panel-default">
      <div class="panel-heading text-center">
        <h3>
          Media Bias Distribution
        </h3>
      </div>
      <div class="panel-body">
        <p>
          The distribution of users' media bias (in percent) divided into groups
          and presented as a bar chart. This is an average for each user and is
          based on a list of media outlet bias found at
          <a href="https://www.allsides.com/bias/bias-ratings" target="_blank">
            AllSides.com
          </a>
          and is used in our algorithmic analysis approach.
          (<a href="" data-toggle="collapse" data-target="#collapseMediaBar"
              onclick="event.preventDefault();">more...</a>)
        </p>
        <div id="collapseMediaBar" class="collapse" style="padding-top: 15px;">
          @yield('barChartDesc')
        </div>
      </div>
      <div class="panel-footer">
        {!! $mediaChartBar->render() !!}
      </div>
    </div>
  </div>
</div>
@endsection