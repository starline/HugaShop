{block name=tabs}

	{if 'finance'|user_access and $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin, PurseListAdmin, PurseAdmin, PurseNewAdmin, FinanceCategoryAdmin, FinanceCategoryListAdmin, FinanceCategoryNewAdmin]}

		<li class="mini {if $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin]}active{/if}">
			<a href="{'PaymentListAdmin'|urll}">Платежи</a>
		</li>

		<li class="mini right {if $route|in_array:[PurseListAdmin, PurseAdmin, PurseNewAdmin]}active{/if}">
			<a href="/admin/finance/purses">Кошелки</a>
		</li>

		<li
			class="mini right {if $route|in_array:[FinanceCategoryAdmin, FinanceCategoryListAdmin, FinanceCategoryNewAdmin]}active{/if}">
			<a href="/admin/finance/categories">Категории платежей</a>
		</li>
	{/if}


	{if 'stats'|user_access and $route|in_array:[StatsAdmin]}
		<li class="mini active">
			<span>Статистика продаж</span>
		</li>
	{/if}

	{if 'finance'|user_access and $route|in_array:[CurrencyAdmin]}
		<li class="mini active">
			<span>Валюты</span>
		</li>
	{/if}


{/block}