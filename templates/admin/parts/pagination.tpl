{if $pagination->pages_count > 1}

	{* Скрипт для листания через ctrl → *}
	{* Ссылки на соседние страницы должны иметь id PrevLink и NextLink *}
	{* ctrlnavigate.js *}

	<!-- Листалка страниц -->
	<nav class="pagination">

		{* Количество выводимых ссылок на страницы *}
		{$visible_pages = 5}

		{* По умолчанию начинаем вывод со страницы 1 *}
		{$page_from = 1}

		{* Отображение навигационных кнопок *}
		{$navigation_btn = false}

		{* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
		{if $pagination->current_page != 'all' AND $pagination->current_page > floor($visible_pages / 2)}
			{$page_from = max(1, $pagination->current_page - floor($visible_pages / 2) - 1)}
		{/if}

		{* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
		{if $pagination->current_page > $pagination->pages_count - ceil($visible_pages / 2)}
			{$page_from = max(1, $pagination->pages_count - $visible_pages - 1)}
		{/if}

		{* До какой страницы выводить - выводим всё окно, но не более общего количества страниц *}
		{$page_to = min($page_from + $visible_pages, $pagination->pages_count - 1)}

		{* Ссылка на 1 страницу отображается всегда *}
		<a class="{if $pagination->current_page == 1}selected{else}droppable{/if}" href="{url page=null}">1</a>

		{* Выводим страницы нашего "окна" *}
		{section name=pages loop=$page_to start=$page_from}

			{* Номер текущей выводимой страницы *}
			{$p = $smarty.section.pages.index + 1}

			{* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
			{if ($p == $page_from + 1 && $p != 2)}
				<a class="between" href="{url page = ceil($p / 2)}">...</a>
			{elseif ($p == $page_to && $p != $pagination->pages_count - 1)}
				<a class="between" href="{url page=$p + ceil(($pagination->pages_count - $p) / 2)}">...</a>
			{else}
				<a class="{if $p == $pagination->current_page}selected{else}droppable{/if}" href="{url page=$p}">{$p}</a>
			{/if}
		{/section}

		{* Ссылка на последнюю страницу отображается всегда *}
		<a class="{if $pagination->current_page == $pagination->pages_count}selected{else}droppable{/if}"
			href="{url page=$pagination->pages_count}">{$pagination->pages_count}</a>

		{if navigation_btn and $pagination->pages_count < 5}
			<a class="navigation_btn {if $pagination->current_page == 'all'}selected{/if}" href="{url page=all}">все сразу</a>
		{/if}

		{if $navigation_btn}
			{if $pagination->current_page > 1}
				<a id="PrevLink" class="navigation_btn" href="{url page=$pagination->current_page - 1}">← назад</a>
			{/if}
			{if $pagination->current_page < $pagination->pages_count}
				<a id="NextLink" class="navigation_btn" href="{url page=$pagination->current_page + 1}">вперед →</a>
			{/if}
		{/if}

	</nav>
{/if}