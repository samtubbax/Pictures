{option:!items}
	<p>{$msgNoItems}</p>
{/option:!items}
{option:items}
		{iteration:items}
			<a href="{$items.full_url}">
				<img src="{$items.preview}" />
				<h3>{$items.title}</h3>
			</a>
		{/iteration:items}
	{include:core/layout/templates/pagination.tpl}
{/option:items}
