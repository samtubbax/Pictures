<h2>{$item.title}</h2>
{$item.text}
<ul>
	{iteration:item.pictures}
		<li><img src="{$item.pictures.full_url}" /></li>
	{/iteration:item.pictures}
</ul>