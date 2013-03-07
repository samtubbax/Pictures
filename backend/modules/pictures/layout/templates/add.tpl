{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblAlbum|ucfirst}: {$lblAdd}</h2>
</div>

{form:add}
	<div class="box">
		<div class="heading"></div>
		<div class="options oneLiner">
			<p class="oneLiner">
				<label for="template">{$lblTemplate|ucfirst}</label>
				{$ddmTemplate} {$ddmTemplateError}
			</p>
		</div>
	</div>

	<div class="tabs">
		<ul>
			{iteration:languages}
				<li><a href="#tab{$languages.language}">{$languages.language|ucfirst}</a></li>
			{/iteration:languages}
		</ul>

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
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblAdd|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}