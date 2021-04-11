@extends('layouts.app')

@section('content')
    <input type="hidden" name="is-guest" value="{{ Auth::guest() ? 'true' : 'false' }}" />
    <div id="react-app"></div>
@endsection
