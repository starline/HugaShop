{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{if $configurator->id}
    {$meta_title = $configurator->name}
{else}
    {$meta_title = 'Новый конфигуратор'}
{/if}

{block name=content}
    <form method="post">
        <input type="hidden" name="id" value="{$configurator->id}" />
        {getCSRFInput}

        <div class="row gx-5">
            <div class="col-12">
                <div class="over_name">
                    <div class="checkbox_line">
                        <div class="form-check form-switch">
                            <input type="hidden" name="enabled" value="0">
                            <input class="form-check-input" name="enabled" value="1" type="checkbox" role="switch"
                                id="enabled" {if $configurator->enabled}checked{/if} />
                            <label class="form-check-label" for="enabled">Активен</label>
                        </div>
                    </div>
                </div>

                <div class="name_row">
                    <div class="col">
                        <div class="input-group has-validation">
                            <span class="input-group-text item_id">#{$configurator->id}</span>
                            <input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
                                name="name" type="text" value="{$configurator->name}" autocomplete="off" />
                            <div class="invalid-feedback">Введите название</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 layer">
                <h2>Описание</h2>
                <textarea name="description" class="form-control" rows="3">{$configurator->description}</textarea>
            </div>
            <div class="col-12 btn_row">
                {include file="parts/button.tpl"}
            </div>
        </div>
    </form>

    {if $configurator->id}
        <h2 class="mt-5">Шаги</h2>
        <form method="post" class="list_form">
            {getCSRFInput}
            <div class="list sortable_on">
                {foreach $steps as $step}
                    <div class="list_row">
                        <div class="move">
                            <div class="move_zone"></div>
                            <input type="hidden" name="positions[{$step->id}]" value="{$step->position}">
                        </div>
                        <div class="row col">
                            <div class="col-12 col-sm-8">
                                <a
                                    href="{'AddonProductConfiguratorStep'|link:[configurator_id=>$configurator->id,id=>$step->id]}">{$step->name}</a>
                            </div>
                            <div class="col-12 col-sm-4 text-end">
                                <span class="badge text-bg-round">{$step->id}</span>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="mt-3">
                {include file="parts/button.tpl" label="Сохранить порядок"}
                <a class="btn btn-outline-primary"
                    href="{'AddonProductConfiguratorStepNew'|link:[configurator_id=>$configurator->id]}">Добавить шаг</a>
            </div>
        </form>
    {/if}
{/block}