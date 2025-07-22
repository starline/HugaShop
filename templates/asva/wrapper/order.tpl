<!DOCTYPE html>
<html lang="ru">
{include file='parts/head.tpl'}

<body>

	<!-- Header -->
	<header class="header_order">
		<div class="container">
			<div class="row g-4">

				<div class="col-12 col-lg-4">
					<div class="logo">
						<a href="/" data-bs-toggle="tooltip"
							title="{$settings->company_name} - {$settings->company_description}">
							<img loading="lazy" alt="{$settings->company_name} - {$settings->company_description}"
								src="{'images/logo.png'|asset}" />
						</a>
					</div>
				</div>

				<div class="col-12 col-lg-8 contact">
					<div class="row">
						{include file='parts/phones.tpl'}
					</div>
				</div>
			</div>
		</div>
	</header>

	<!-- Body -->
	<main>
		<div class="container">
			{block name=content}{/block}
		</div>
	</main>

	{block name=body_script}{/block}

	{extension place='front_body'}

</body>

</html>