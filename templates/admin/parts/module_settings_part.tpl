{foreach $modules as $module}
    {$module_key = $module@key}
    <div class="module_settings" {if $module_key != ${$module_type}->module}style="display:none;" {/if}
        module="{$module_key}">

        <h2>{$module->name}</h2>

        {* Параметры модуля *}
        <ul class="property_block">
            {foreach $module->settings as $setting}
                {if count((array)$setting->options) > 1}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting->variable}">{$setting->name}</label>
                        <select class="form-select" name="{$module_type}_settings[{$setting->variable}]"
                            id="{$module_key}-{$setting->variable}">
                            {foreach $setting->options as $option}
                                <option value='{$option->value}' {if $option->value == ${$module_type}->settings->
                                    {$setting->variable}}selected{/if}>
                                    {$option->name}</option>
                            {/foreach}
                        </select>
                    </li>
                {elseif count((array)$setting->options) == 1}
                    {$option = $setting->options|@first}
                    <li>
                        <label class="form-check-label" for="{$module_key}-{$setting->variable}">{$setting->name}</label>
                        <input class="form-check-input" name="{$module_type}_settings[{$setting->variable}]" type="checkbox"
                            value="{$option->value}"
                            {if $option->value == ${$module_type}->settings->{$setting->variable}}checked{/if}
                        id="{$module_key}-{$setting->variable}" />
                    </li>


                {elseif !empty($setting->type) and $setting->type == "file"}
                    {* File upload *}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting->variable}">{$setting->name}</label>
                        <input class="form-control" name="{$setting->variable}" type="file"
                            id="{$module_key}-{$setting->variable}" />
                    </li>

                    {if ${$module_type}->settings->{$setting->variable}}
                        <li>
                            <img
                                src="{$config->root_url}/{${$module_type}->settings->{$setting->variable}}?{math equation='rand(10,10000)'}" />
                        </li>
                    {/if}
                {else}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting->variable}">{$setting->name}</label>
                        <input class="form-control" name="{$module_type}_settings[{$setting->variable}]" type="text"
                            value="{${$module_type}->settings->{$setting->variable}}" id="{$module_key}-{$setting->variable}"
                            {if !$setting->placeholder|empty}placeholder="{$setting->placeholder}" {/if} />
                    </li>
                {/if}
            {/foreach}
        </ul>
    </div>
{/foreach}

<div class="module_settings" {if ${$module_type}->module != ''}style="display:none;" {/if} module=""></div>

<script type="module">
    {literal}

        // On document load
        $(function() {
            $('div.module_settings').filter(':hidden').find("input, select, textarea").prop("disabled", true);
            $('select[name=module]').change(function() {
                $('div.module_settings').hide().
                find("input, select, textarea").prop("disabled", true);

                $("div.module_settings[module='" + $(this).val() + "']").
                show().find("input, select, textarea")
                    .prop("disabled", false);
            });
        });
    {/literal}
</script>