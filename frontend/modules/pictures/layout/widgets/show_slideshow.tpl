<section id="picturesWidgetSlideshow">
	<ul>
		{iteration:widgetPictures.slides.pictures}
		<li>
			<a href="{$widgetPictures.slides.pictures.image_source}">
				<img src="{$widgetPictures.slides.pictures.image_thumbnail}" alt="{$widgetPictures.slides.pictures.title}">
			</a>
		</li>
		{/iteration:widgetPictures.slides.pictures}
	</ul>
</section>