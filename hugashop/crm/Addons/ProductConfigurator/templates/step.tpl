{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{if $step->id}
    {$meta_title = $step->name}
{else}
    {$meta_title = 'Новый шаг'}
{/if}

{block name=content}
    <form method="post">
        <input type="hidden" name="id" value="{$step->id}" />
        <input type="hidden" name="configurator_id" value="{$configurator_id}" />
        {getCSRFInput}

        <div class="row gx-5">
            <div class="col-12">
                <div class="name_row">
                    <span class="col-form-label item_id">#{$step->id}</span>
                    <input class="form-control form-control-lg" name="name" type="text" value="{$step->name}" />
                </div>
            </div>
            <div class="col-12 layer">
                <h2>Описание</h2>
                <textarea name="description" class="form-control" rows="3">{$step->description}</textarea>
            </div>
            <div class="col-12 layer">
                <h2>Картинка</h2>
                <input class="form-control" name="image" type="text" value="{$step->image}" />
            </div>
            <div class="col-12 layer">
                <h2>Опции</h2>
                <div id="options">
                    {foreach $options as $opt}
                        <div class="row g-2 option_row mb-2">
                            <input type="hidden" name="option_id[]" value="{$opt->id}">
                            <div class="col-6"><input class="form-control" name="option_name[]" value="{$opt->name}"></div>
                            <div class="col-4"><input class="form-control" name="option_price[]" value="{$opt->price}"></div>
                            <div class="col-2"><button class="btn btn-outline-danger remove_option" type="button">&times;</button></div>
                        </div>
                    {/foreach}
                </div>
                <button type="button" class="btn btn-secondary mt-2" id="add_option">Добавить опцию</button>
            </div>
            <div class="col-12 btn_row">
                {include file="parts/button.tpl"}
            </div>
        </div>
    </form>
{/block}

{block name=body_script append}
    <script type="module">
        {literal}
        $(function(){
            $('#add_option').click(function(){
                $('#options').append('<div class="row g-2 option_row mb-2"><input type="hidden" name="option_id[]" value=""><div class="col-6"><input class="form-control" name="option_name[]"></div><div class="col-4"><input class="form-control" name="option_price[]" value="0"></div><div class="col-2"><button class="btn btn-outline-danger remove_option" type="button">&times;</button></div></div>');
            });
            $(document).on('click','.remove_option',function(){ $(this).closest('.option_row').remove(); });
        });
        {/literal}
    </script>
{/block}
