@php the_content() @endphp
    @include('partials.extrabtn', App::extra_btn())
{!! wp_link_pages(['echo' => 0, 'before' => '<nav class="page-nav"><p>' . __('Pages:', 'sage'), 'after' => '</p></nav>']) !!}
