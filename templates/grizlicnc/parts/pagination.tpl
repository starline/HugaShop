{if $pages_count > 1}

	<!-- Листалка страниц -->
	<div class="pagination my-5">

		{* Количество выводимых ссылок на страницы *}
		{$visible_pages = 3}

		{* По умолчанию начинаем вывод со страницы 1 *}
		{$page_from = 1}

		{* Если выбранная пользователем страница дальше середины "окна" - начинаем вывод уже не с первой *}
		{if $current_page > floor($visible_pages / 2)}
			{$page_from = max(1, $current_page - floor($visible_pages / 2) - 1)}
		{/if}

		{* Если выбранная пользователем страница близка к концу навигации - начинаем с "конца-окно" *}
		{if $current_page > $pages_count - ceil($visible_pages / 2)}
			{$page_from = max(1, $pages_count - $visible_pages - 1)}
		{/if}

		{* До какой страницы выводить - выводим всё окно, но не более ощего количества страниц *}
		{$page_to = min($page_from + $visible_pages, $pages_count - 1)}

		{* Ссылка на 1 страницу отображается всегда *}
		<a {if $current_page==1}class="selected" {/if} href="{url page=null}">1</a>

		{* Выводим страницы нашего "окна" *}
		{section name=pages loop=$page_to start=$page_from}

			{* Номер текущей выводимой страницы *}
			{$p = $smarty.section.pages.index+1}

			{* Для крайних страниц "окна" выводим троеточие, если окно не возле границы навигации *}
			{if ($p == $page_from + 1 && $p != 2)}
				<a class="between" href="{url page = ceil($p / 2)}">...</a>
			{elseif ($p == $page_to && $p != $pages_count - 1)}
				<a class="between" href="{url page = $p + ceil(($pages_count - $p) / 2)}">...</a>
			{else}
				<a class="{if $p == $current_page}selected{/if}" href="{url page=$p}">{$p}</a>
			{/if}
		{/section}

		{* Ссылка на последнююю страницу отображается всегда *}
		<a {if $current_page == $pages_count}class="selected" {/if} href="{url page=$pages_count}">{$pages_count}</a>

	</div>
{/if}