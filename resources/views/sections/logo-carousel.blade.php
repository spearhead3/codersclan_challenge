<div class="carousel-container">
	<div id="carouselIndicators" class="carousel slide" data-ride="carousel" data-interval=3000>
		<ol class="carousel-indicators">
			@for ($i = 0; $i < $count; $i++)
			<li data-target="#carouselIndicators" data-slide-to="{{$i}}" 
			@if ($i == 0)
			class="active"
			@endif
			></li>
			@endfor
		</ol>
		<div class="carousel-inner">
			@for ($i = 0; $i < $count; $i++)
			<div class="carousel-item 
			@if ($i == 0)
			active
			@endif
			">
				<img class="d-block w-100" src="{{$logo[$i]}}" title="{{$text[$i]}}">
			</div>
			@endfor
		</div>
		<a class="carousel-control-prev" href="#carouselIndicators" role="button" data-slide="prev">
			<span class="carousel-control-prev-icon" aria-hidden="true"></span>
			<span class="sr-only">Previous</span>
		</a>
		<a class="carousel-control-next" href="#carouselIndicators" role="button" data-slide="next">
			<span class="carousel-control-next-icon" aria-hidden="true"></span>
			<span class="sr-only">Next</span>
		</a>
	</div>
</div>