<ul>
    <li class="category_main">
        <a class="{if $route|in_array:[PostList, Post]}selected{/if}" href="{'PostList'|linkLang}">База
            знаний</a>
    </li>

    {foreach 'ContentPage'|api:getListTranslate:[[visible => 1], position] as $pm}
        <li class="category_main">
            <a class="{if (!$page|empty && $page->id == $pm->id)}selected{/if}"
                href="{'Page'|linkLang:[url => $pm->url]}">{$pm->name}</a>
        </li>
    {/foreach}
</ul>