{block name=tabs}

    {if 'addon'|user_access}
        <li class="mini {if $route|in_array:[AddonListAdmin]}active{/if}">
            <a href="{'AddonListAdmin'|link}">Модули</a>
        </li>
    {/if}


    {if 'addon'|user_access and $addon->module}
        <li class="mini {if $route != 'AddonSettingsAdmin'}active{/if}">
            <a href="{'AddonAdmin'|link:[name => $addon->module]}">{$addon->name}</a>
        </li>
    {/if}

    {if $addon->settings_params and 'addon'|user_access}
        <li class="mini right {if $route == 'AddonSettingsAdmin'}active{/if}">
            <a href="{'AddonSettingsAdmin'|link:[name => $addon->module]}">Настройки</a>
        </li>
    {/if}

{/block}