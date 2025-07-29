{foreach $modules as $module}
    {$module_key = $module@key}
    <div class="module_settings" {if $module_key != ${$module_type}->module}style="display:none;" {/if}
        module="{$module_key}">

        <h2>{$module->name}</h2>
        
        {* Параметры модуля *}
        <ul class="property_block">
            {foreach $module->settings_params as $setting_param}
                {$setting_value = ''}
                {if isset(${$module_type}->settings->{$setting_param->variable})}
                    {$setting_value = ${$module_type}->settings->{$setting_param->variable}}
                {elseif isset($setting_param->default)}
                    {$setting_value = $setting_param->default}
                {/if}
                {if count((array)$setting_param->options) > 1}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting_param->variable}">{$setting_param->name}</label>
                        <select class="form-select" name="{$module_type}_settings[{$setting_param->variable}]"
                            id="{$module_key}-{$setting_param->variable}">
                            {foreach $setting_param->options as $option}
                                <option value='{$option->value}' {if $option->value == $setting_value}selected{/if}>
                                    {$option->name}</option>
                            {/foreach}
                        </select>
                    </li>
                {elseif count((array)$setting_param->options) == 1}
                    {$option = $setting_param->options|@first}
                    <li>
                        <label class="form-check-label"
                            for="{$module_key}-{$setting_param->variable}">{$setting_param->name}</label>
                        <input class="form-check-input" name="{$module_type}_settings[{$setting_param->variable}]" type="checkbox"
                            value="{$option->value}"
                            {if $option->value == $setting_value}checked{/if}
                        id="{$module_key}-{$setting_param->variable}" />
                    </li>


                {elseif !empty($setting_param->type) and $setting_param->type == "file"}
                    {* File upload *}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting_param->variable}">{$setting_param->name}</label>
                        <input class="form-control" name="{$setting_param->variable}" type="file"
                            id="{$module_key}-{$setting_param->variable}" />
                    </li>

                    {if ${$module_type}->settings->{$setting_param->variable}}
                        <li>
                            <img
                                src="{$config->root_url}/{${$module_type}->settings->{$setting_param->variable}}?{math equation='rand(10,10000)'}" />
                        </li>
                    {/if}
                {else}
                    <li>
                        <label class="col-form-label" for="{$module_key}-{$setting_param->variable}">{$setting_param->name}</label>
                        <input class="form-control" name="{$module_type}_settings[{$setting_param->variable}]" type="text"
                            value="{$setting_value}"
                            id="{$module_key}-{$setting_param->variable}"
                            {if !$setting_param->placeholder|empty}placeholder="{$setting_param->placeholder}" {/if} />
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