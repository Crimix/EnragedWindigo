@include('layouts.components.header')
  <div id="app">
    @include('layouts.components.navbar')
    @yield('content')
  </div>
@include('layouts.components.footer')