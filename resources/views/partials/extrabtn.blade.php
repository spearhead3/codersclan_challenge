<button 
@if ($default_bg == 'custom')
class="ext-button"
style="background-color: {{$bg}} !important;" 
@else
class="my-button" 
@endif
>{{$text}}</button>