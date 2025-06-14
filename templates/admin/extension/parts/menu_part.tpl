{block name=tabs}

	{if 'extension'|user_access}
		<li class="mini {if $route|in_array:[ExtensionListAdmin]}active{/if}">
			<a href="/admin/extensions">Модули</a>
		</li>
	{/if}


	{if !$extension->module|empty and 'extension'|user_access}
		<li class="mini {if $route|in_array:[ExtensionAdmin, ExtensionItemNewAdmin, ExtensionItemAdmin]}active{/if}">
			<a href="/admin/extension/{$extension->module}">{$extension->name}</a>
		</li>
	{/if}

{/block}