{block name=tabs}

	{if 'warehouse'|user_access}
		<li
			class="mini {if $route|in_array:[MoveAdmin, MoveListAdmin, MoveNewAdmin]}active{/if}">
			<a href="/admin/warehouse/moves">Поставки</a>
		</li>
	{/if}


	{if 'warehouse_provider'|user_access}
		<li class="mini right {if $route|in_array:[ProviderListAdmin, ProviderAdmin]}active{/if}">
			<a href="/admin/warehouse/providers">Поставщики</a>
		</li>
	{/if}

	{if 'warehouse_place'|user_access}
		<li class="mini right {if $route|in_array:[PlaceListAdmin, PlaceAdmin, PlaceNewAdmin]}active{/if}">
			<a href="/admin/warehouse/places">Склады</a>
		</li>
	{/if}

{/block}