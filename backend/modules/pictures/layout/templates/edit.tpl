{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblAlbum|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<p>
		<label for="title">{$lblTitle|ucfirst}</label>
		{$txtTitle} {$txtTitleError}
	</p>

	<div class="box">
		<div class="heading"></div>
		<div class="options oneLiner">
			<label for="template">{$lblTemplate|ucfirst}</label>
			{$ddmTemplate} {$ddmTemplateError}
		</div>
	</div>

	<fieldset>
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
	</fieldset>

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
			{$msgConfirmDelete|sprintf:{$item.title}}
		</p>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}