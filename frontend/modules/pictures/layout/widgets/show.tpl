<section id="slideshow" class="carousel slide">
	<div class="carousel-inner">
		{iteration:widgetPictures.pictures}
		<div class="item">
			<img src="{$widgetPictures.pictures.image_source}" alt="{$widgetPictures.pictures.title}">
			<div class="carousel-caption">
				<h1>{$widgetPictures.pictures.title}</h1>
				<h3>{$widgetPictures.pictures.tagline}</h3>
			</div>
		</div>
		{/iteration:widgetPictures.pictures}
	</div>
</section>

<div id="sliderPagination">
	<ul class="clearfix">
	{iteration:widgetPictures.pictures}
		<li><a class="pagination" href="#" data-id="{$widgetPictures.pictures.index}">{$widgetPictures.pictures.index}</a></li>
	{/iteration:widgetPictures.pictures}
	</ul>
</div>
<div id="indexNumber">

</div>