/**
* Pictures functionality for backend
*
* @author Sam Tubbax <sam@sumocoders.be>
*/
jsBackend.pictures = {
		getUniqueIndex: function (i) {
			if($('#image_upload_' + i).length > 0)
			{
				return jsBackend.pictures.getUniqueIndex(i + 1);
			}

			return i;
		},

		init: function () {
			jsBackend.pictures.tableSequence.init();

			$('#addPicture').click(function (e) {
				e.preventDefault();
				var index = $('.imageGrid tr').length;
				index = jsBackend.pictures.getUniqueIndex(index);

				var $newImage = $('.imageGrid tr:last').clone();
				$newImage.show();
				$newImage.find('img').remove();
				$newImage.find('input.fileField').prop('id', 'image_upload_' + index).prop('name', 'image_upload_' + index).show().val('');
				$newImage.find('input.imageField').prop('id', 'image_' + index).prop('name', 'image_' + index).val('');
				$newImage.find('input.titleField').prop('id', 'image_title_' + index).prop('name', 'image_title_' + index).val('');
				$newImage.find('input.urlField').prop('id', 'image_url_' + index).prop('name', 'image_url_' + index).val('');
				$newImage.find('input.sequenceField').prop('id', 'sequence_' + index).prop('name', 'sequence_' + index).val($('.imageGrid tr').length + 1);
				$newImage.find('.iconDelete').show();

				$('.imageGrid tr:last').before($newImage);
			});


			$('.imageGrid .iconDelete').live('click', function (e) {
				e.preventDefault();
				$(this).parents('.imageGrid tr').remove();
			});

			$('.imageGrid tr').each(function () {
				if($(this).find('.error').html() != '' && $(this).find('.imageField').val() == '')
				{
					$(this).find('input[type="file"]').show().addClass('inputFileError');
				}
			});

			$('.imageGrid tr:last').hide();

			// search client
			$('#category').autocomplete({
				source: function(request, response)
				{
					$.ajax(
					{
						data:
						{
							fork: { action: 'autocomplete' },
							term: request.term
						},
						success: function(data, textStatus)
						{
							// set response
							response(data.data);
						}
					});
				},
				select: function (e, ui) {
					$('#categoryTxt').val(ui.item.value);
				}
			});


		}
}


jsBackend.pictures.tableSequence =
{
	// init, something like a constructor
	init: function()
	{
		var i = 0;
		var rows = $(this).find('tr');
		$(this).find('.sequenceField').val(i);
		i++;

		if($('.imageGrid tbody').length > 0)
		{
			$('.imageGrid tbody').sortable(
			{
				items: 'tr',
				handle: 'td.dragAndDropHandle',
				placeholder: 'dragAndDropPlaceholder',
				forcePlaceholderSize: true,
				stop: function(event, ui)
				{
					// the table
					var table = $(this);
					var action = (typeof $(table.parents('table.dataGrid')).data('action') == 'undefined') ? 'sequence' : $(table.parents('table.dataGrid')).data('action').toString();

					// fetch extra parameters
					if(typeof $(table.parents('table.dataGrid')).data('extra-params') != 'undefined') url += $(table.parents('table.dataGrid')).data('extra-params');

					// init var
					var rows = $(this).find('tr');
					var newIdSequence = new Array();

					var i = 0;
					// loop rowIds
					rows.each(function() {
						$(this).find('.sequenceField').val(i);
						i++;
					});
				}
			});
		}
	},


	// end
	eoo: true
}

$(jsBackend.pictures.init);