<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width minimum-scale=0.5 maximum-scale=3 shrink-to-fit=no">
	<meta name="theme-color" content="#6b514c">

	<meta name="description" content="{$meta_description}">
	<title>{$meta_title}</title>

	<link rel="shortcut icon" type="image/x-icon" href="{'images/favicon.ico'|asset}">

	<meta name="language" content="{$current_language->code}">

	{foreach from=$languages item=lang}
		<link rel="alternate" hreflang="{$lang->code}" href="{'current'|linkLang:['locale' => $lang->code]}" />
	{/foreach}

	{if $main_language}
		<link rel="alternate" hreflang="x-default" href="{'current'|linkLang:['locale' => $main_language->code]}" />
	{/if}

	{if !$canonical|empty}
		<link rel="canonical" href="{$config->root_url}{$canonical}" />
	{/if}

	{if !$noindex|empty}
		<meta name="robots" content="noindex">
	{/if}

	{importmap point='grizlicnc'}

	{block name=head_css}{/block}
	{block name=head_script}{/block}

	{extension place='front_head'}

</head>