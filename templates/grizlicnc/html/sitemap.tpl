<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
    {foreach $paths as $path}
        {foreach $languages as $language}
            {$lang_prefix = ''}
            {if $language->code !== $main_language->code}{$lang_prefix = '/'|cat:$language->code}{/if}
            {$loc = $root_url|cat:$lang_prefix|cat:$path}
            <url>
                <loc>{$loc|escape}</loc>
                {foreach $languages as $alt_language}
                    {$alt_prefix = ''}
                    {if $alt_language->code !== $main_language->code}{$alt_prefix = '/'|cat:$alt_language->code}{/if}
                    <xhtml:link rel="alternate" hreflang="{$alt_language->code|escape}" href="{$root_url}{$alt_prefix}{$path|escape}" />
                {/foreach}
                <xhtml:link rel="alternate" hreflang="x-default" href="{$root_url}{$path|escape}" />
                <lastmod>{$today}</lastmod>
            </url>
        {/foreach}
    {/foreach}
</urlset>

