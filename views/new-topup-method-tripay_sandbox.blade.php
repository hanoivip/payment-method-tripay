@extends('hanoivip::layouts.app')

@section('title', 'Topup with Tripay')

@section('content')

@if (!empty($guide))
	<p>{{$guide}}</p>
@endif

@foreach ($data as $i => $channel)
	<form method="post" action="{{route('newtopup.do')}}">
    	{{ csrf_field() }}
    	<input type="hidden" name="trans" id="trans" value="{{$trans}}" />
    	<input type="hidden" name="channel" id="channel" value="{{$channel['code']}}" />
    	<button type="submit"> {{ $channel['name'] }} </button>
    </form>
@endforeach

@endsection
