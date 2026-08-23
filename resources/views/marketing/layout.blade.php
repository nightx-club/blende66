@extends('layouts.app')
@section('content')
<div class="marketing-shell min-h-screen bg-[#f8f5ef]">
    @include('partials.public-navigation')

    @yield('marketing-content')

    @include('partials.public-footer')
</div>
@endsection
