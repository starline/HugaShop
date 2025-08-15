{block name=tabs}

	{if 'finance'|user_access and $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin, PurseListAdmin, PurseAdmin, PurseNewAdmin, FinanceCategoryAdmin, FinanceCategoryListAdmin, FinanceCategoryNewAdmin]}

		<li class="mini {if $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin]}active{/if}">
			<a href="{'PaymentListAdmin'|link}">Платежи</a>
		</li>

                <li class="mini right {if $route|in_array:[PurseListAdmin, PurseAdmin, PurseNewAdmin]}active{/if}">
                        <a href="{'PurseListAdmin'|link}">Кошелки</a>
                </li>

		<li
                        class="mini right {if $route|in_array:[FinanceCategoryAdmin, FinanceCategoryListAdmin, FinanceCategoryNewAdmin]}active{/if}">
                        <a href="{'FinanceCategoryListAdmin'|link}">Категории платежей</a>
                </li>
	{/if}


	{if 'stats'|user_access and $route|in_array:[StatsAdmin]}
		<li class="mini active">
			<a href="{'StatsAdmin'|link}">Статистика продаж</a>
		</li>
	{/if}


	{if 'finance'|user_access and $route|in_array:[CurrencyAdmin]}
		<li class="mini active">
			<a href="{'CurrencyAdmin'|link}">Валюты</a>
		</li>
	{/if}

{/block}