{block name=tabs}

    {if 'extension'|user_access}
        <li class="mini {if $route|in_array:[ExtensionListAdmin]}active{/if}">
            <a href="{'ExtensionListAdmin'|link}">Модули</a>
        </li>
    {/if}


    {if 'extension'|user_access and $extension->module}
        <li class="mini {if $route != 'ExtensionSettingsAdmin'}active{/if}">
            <a href="{'ExtensionAdmin'|link:[name => $extension->module]}">{$extension->name}</a>
        </li>
    {/if}

    {if $extension->settings_params and 'extension'|user_access}
        <li class="mini right {if $route == 'ExtensionSettingsAdmin'}active{/if}">
            <a href="{'ExtensionSettingsAdmin'|link:[name => $extension->module]}">Настройки</a>
        </li>
    {/if}

{/block}