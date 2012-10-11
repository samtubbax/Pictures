jsFrontend.pictures = {
	init: function () {
		$('#slideshow').carousel({
			interval: 6000,
			pause: "hover"
		});

		$('a > img').each(function() {
			$(this).parent().addClass('linkedImage');
		});

		if($('.carousel-inner > p')) {
			$('.carousel-inner > p').remove();
		}

		$('.item:first-child').addClass('active');

		$('.pagination').on('click', function (e) {
			e.preventDefault();
			$('#slideshow').carousel('pause');
			$('#slideshow').carousel($(this).data('id'));
		});

		jsFrontend.pictures.setBullet();

		$('#slideshow').on('slid', setBullet);
	},

	setBullet: function() {
		// get the index of the active slide
		var indexNumber = $('.item.active').index();
		// remove 'active' class from all pagination bullets
		$('#sliderPagination a').removeClass('active');
		// add 'active' class to pagination bullet with same index as active slide
		$('#sliderPagination a:eq('+ indexNumber + ')').addClass('active');
	}
}