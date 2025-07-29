<!DOCTYPE html>
<html>
{include file='parts/head.tpl'}

<body>

	<!-- Header -->
	<div id="header" class="container my-4">
		<div class="row content">
			<div class="col-lg-6 logo">
				<a href="/" data-bs-toggle="tooltip"
					title="{$settings->company_name} - {$settings->company_description}">
					<img loading="lazy" alt="{$settings->company_name} - {$settings->company_description}"
						src="{'images/logo.png'|asset}" />
				</a>
			</div>

			<div class="col-lg-6 contact">
				{include file='parts/phones.tpl'}
			</div>
		</div>
	</div>

	<div class="container my-4">
		{block name=content}{/block}
	</div>

	{extension place='front_body'}

</body>

</html>