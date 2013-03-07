{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblAlbum|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<div class="box">
		<div class="heading"></div>
		<div class="options">
			<p class="oneLiner">
				<label for="template">{$lblTemplate|ucfirst}</label>
				{$ddmTemplate} {$ddmTemplateError}
			</p>
			<p>
				<label for="preview">{$lblCover|ucfirst}</label>
				{$filePreview} {$filePreviewError}
			</p>
			{option:item.preview}
				<img src="/frontend/files/pictures/100x/{$item.preview}"
			{/option:item.preview}

		</div>
	</div>

	<div class="tabs">
		<ul>
			<li><a href="#tabImages">{$lblImages|ucfirst}</a></li>
			{iteration:languages}
				<li><a href="#tab{$languages.language}">{$languages.language|ucfirst}</a></li>
			{/iteration:languages}
		</ul>

		<div id="tabImages">
			<div class="box horizontal">
				<div class="heading">
					<h3>
						{$lblImages|ucfirst}
					</h3>
				</div>
				<div class="options imageGrid">
					{$imageDatagrid}

					<p><a href="#" id="addPicture" class="button icon iconAdd iconOnly"><span>toevoegen</span></a></p>
				</div>
			</div>
		</div>
		{iteration:languages}
		<div id="tab{$languages.language}"
			<p>
				<label for="title{$languages.language|ucfirst}">{$lblTitle|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
				{$languages.txtTitle} {$languages.txtTitleError}
			</p>
			<div class="box">
				<div class="heading">
					<label for="text{$languages.language|ucfirst}">{$lblText|ucfirst}</label>
				</div>
				<div class="optionsRTE">
						{$languages.txtText} {$languages.txtTextError}
				</div>
			</div>
		</div>
		{/iteration:languages}

	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'delete'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
			<span>{$lblDelete|ucfirst}</span>
		</a>
		<div class="buttonHolderRight">
			<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblSave|ucfirst}" />
		</div>
	</div>

	<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDelete|sprintf:{$item.languages.en.title}}
		</p>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}