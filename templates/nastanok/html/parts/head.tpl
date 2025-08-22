<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width minimum-scale=0.5 maximum-scale=3 shrink-to-fit=no">
	<meta name="theme-color" content="#6b514c" />

	<meta name="description" content="{$meta_description}" />
	<title>{$meta_title}</title>

	<link href="{'images/favicon.ico'|asset}" rel="icon" type="image/x-icon">
	<link href="{'images/favicon.ico'|asset}" rel="shortcut icon" type="image/x-icon">

	{if !$canonical|empty}
		<link rel="canonical" href="{$config->root_url}{$canonical}">
	{/if}

	{if !$noindex|empty}
		<meta name="robots" content="noindex" />
	{/if}

	<!-- CSS -->
	{$css_files[] = "css/bootstrap.min.css"|asset}
	{$css_files[] = "js/fancybox/jquery.fancybox.min.css"|asset}
	{$css_files[] = "js/owlcarousel/owl.carousel.css"|asset}
	{$css_files[] = "css/common.css"|asset}

	{foreach $css_files as $file}
		<link rel="stylesheet" href="{$file}" />
	{/foreach}

	<!-- JS -->
	{$js_files[] = "js/jquery/jquery.js"|asset}
	{$js_files[] = "js/fancybox/jquery.fancybox.min.js"|asset}
	{$js_files[] = "js/autocomplete/jquery.autocomplete-min.js"|asset}
	{$js_files[] = "js/jquery/jquery.form.js"|asset}
	{$js_files[] = "js/owlcarousel/owl.carousel.min.js"|asset}
	{$js_files[] = "js/bootstrap.bundle.min.js"|asset}
	{$js_files[] = "js/common.js"|asset}

	{foreach $js_files as $file}
		<script type="text/javascript" src="{$file}"></script>
	{/foreach}

	{addon place='front_head'}

</head>