<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width initial-scale=1">

	<title>{$meta_title}</title>

	<link rel="icon" href='{"images/favicon.ico"|asset:"{$settings->theme}/assets"}' type="image/x-icon" />
	<meta name="robots" content="noindex">

	{importmap point='admin'}

	<script type="module">
		window.csrf = "{setCSRF}";

		{if $locked_key}
			import { initEditLock } from '{"js/lock.edit.js"|asset}';
			initEditLock({
				lock_url: "{'LockEditAjax'|linkLang:[locked_key => $locked_key]}",
				unlock_url: "{'UnlockEditAjax'|linkLang:[locked_key => $locked_key]}"
			});
		{/if}
	</script>

	{block name=head_css}{/block}
	{block name=head_script}{/block}

</head>

<body>
	<div class="wrap d-xxl-flex">


		<!-- Sidebar -->
		<div class="navbar_vertical navbar d-flex flex-column navbar-expand-xxl">
			<div class="container flex-xxl-column ps-xxl-4">

				<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
					<span class="navbar-toggler-icon"></span>
				</button>

				<div class="current_user pt-xxl-4">
					<a class="user_name" href="{'UserAdmin'|linkLang:['id' => $user->id]}">{$user->name}</a>
					<a href="{'UserLogout'|linkLang}" id="logout">{'user.logout'|trans}</a>
				</div>

				<div class="offcanvas offcanvas-start" id="sidebar">
					<div class="offcanvas-header">
						<h5 class="offcanvas-title" id="offcanvasLabel"></h5>
						<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
					</div>
					<div class="offcanvas-body">
						{include file='parts/side_menu_part.tpl'}
					</div>
				</div>
			</div>
		</div>


		<!-- Content -->
		<div class="flex-grow-1 pt-3">
			<div class="container">

				{include file='parts/service_messages_part.tpl'}

				<ul id="tab_menu" class="overflow-x-auto">
					{block name=tabs}{/block}
				</ul>

				<main class="main_content">
					{block name=subtabs}{/block}
					{block name=content}{/block}
				</main>

				<footer class="mt-4 mb-4">
					<span>HugaShop <snan class="badge text-bg-round">v{$config->version}</snan></span>
					<span>Время <span class="badge text-bg-round">{'now'|date} {'now'|time}</span></span>
				</footer>
			</div>
		</div>

	</div>


	<!-- Body Script -->
	{block name=body_script}{/block}


	<!-- Admin Bookmark -->
	<a class="admin_bookmark" href="{'Main'|linkLang}" data-bs-toggle="tooltip" title="Перейти на сайт">
		<svg viewBox="0 0 24 24" focusable="false" class="dyAbMb">
			<path d="M0 0h24v24H0z" fill="none"></path>
			<path d="M8.59,16.59L13.17,12L8.59,7.41L10,6l6,6l-6,6L8.59,16.59z"></path>
		</svg>
	</a>
</body>

</html>