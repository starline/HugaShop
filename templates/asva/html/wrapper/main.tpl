<!DOCTYPE html>
<html lang="{$current_language->code}">
{include file='parts/head.tpl'}

<body class="tire-home-page">

	<!-- Header -->
	{include file='parts/header.tpl'}

	<div class="header-under-wrapper hidden-phone">
		<div class="header-under asva-container">
			<div class="header-under-links">

				<!-- $APPLICATION->IncludeFile("/bitrix/templates/main_2019/parts/promo_line_part.php") -->
			</div>
		</div>
	</div>

	<div class="main-wrapper">
		<div class="main asva-container">
			{block name=content}{/block}
		</div>

		<div class="main asva-container">
			<!--asva:seo.relevant_links::default -->
		</div>
	</div>


	<!-- Footer -->
	{include file='parts/footer.tpl'}

	{if $user->manager}
		<a class="admin_bookmark" href="{'MainAdmin'|link}" data-bs-toggle="tooltip" title="Перейти в админку">
			<svg viewBox="0 0 24 24" focusable="false">
				<path d="M0 0h24v24H0z" fill="none"></path>
				<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
			</svg>
		</a>
	{/if}

	{block name=body_script}{/block}

	{addon place='front_body'}

</body>

</html>