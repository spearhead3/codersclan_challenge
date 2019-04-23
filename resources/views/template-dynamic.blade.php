{{--
  Template Name: Dynamic Template
--}}

@extends('layouts.app')
@php $val = App::like_or_not() @endphp
@if ($val !== 0)
	@include('partials.warning', $val)
@endif
@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.sections')
  @endwhile
  @include('sections.logo-carousel', App::carousel())
  @include('partials.extrabtn', App::extra_btn())
@endsection
