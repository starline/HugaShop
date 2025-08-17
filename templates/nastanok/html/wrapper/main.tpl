<!DOCTYPE html>
<html lang="ru">

{include file='parts/head.tpl'}

<body>

	{include file='parts/header.tpl'}

	<!-- Body -->
	<div class="container" id="main">

		{block name=content}{/block}

		{if $seo->body}
			<div class="description_html">
				{$seo->body|raw}
			</div>
		{/if}

	</div>

	{include file='parts/footer.tpl'}

	{if $user->manager}
		<a class="admin_bookmark" href="{'MainAdmin'|link}" data-bs-toggle="tooltip" title="Перейти в админку">
			<svg viewBox="0 0 24 24" focusable="false" class="dyAbMb">
				<path d="M0 0h24v24H0z" fill="none"></path>
				<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
			</svg>
		</a>
	{/if}

	{extension place='front_body'}

</body>

</html>