{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='Интерфейс админки'}

{block name=content}

    {$param = 1050}
    {$title = 'Заголовок'}

    <!-- Заголовок -->
    <div class="header_top">
        <h1 class="total_amount">{$title}
            <div class="currency_amount">
                <span class="sum_total">{$param} <span class="sum_profit_price">
                        {$param|plural:'файл':'файлов':'файла'}</span>
                </span>
            </div>
            <div class="currency_amount">
                <span class="sum_total">{$param} <span class="sum_profit_price">шт</span>
                </span>
            </div>
        </h1>
    </div>


    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        {getCSRFInput}

        <div class="row gx-5">



            <!-- Simple List -->
            <div class="col-lg-6 layer">
                <h2>Simple list</h2>
                <div id="list_name" class="list">
                    <div class="list_row">
                        <div class="col">
                            <a href="#">Название</a>
                            <div class="notice">Примечание, комментарий, заметки</div>
                        </div>
                        <div>
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                    <div class="list_row">
                        <div class="col">
                            <a href="#">Название второй позиции</a>
                            <div class="notice">Примечание, комментарий, заметки для второй позиции</div>
                        </div>
                        <div>
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                    <div class="list_row">
                        <div class="col">
                            <a href="#">Название третьей позиции</a>
                            <div class="notice">Примечание, комментарий, заметки для третьей позиции</div>
                        </div>
                        <div>
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Simple List -->



            <!-- Param Value/ Block with params value -->
            <div class="col-lg-6 layer">
                <h2>Заголовок</h2>
                <ul class="property_block">
                    <li>
                        <div class="col-form-label">Размер папки</div>
                        <div class="col-form-label">125 мб</div>
                    </li>
                    <li>
                        <div class="col-form-label">Количество файлов</div>
                        <div class="col-form-label">10000 шт</div>
                    </li>
                </ul>
                <div class="col-12 btn_row">
                    {include file="parts/button.tpl" label="Custom Кнопка" class="btn-success" type="submit" extra_attrs='id=save_btn data-action=save value=1'}
                </div>
            </div>
            <!-- End Param Value -->



            <!-- Param Input. Block with inputs -->
            <div class="col-lg-6 layer">
                <h2>Title</h2>
                <ul class="property_block">
                    <li>
                        <label class="col-form-label" for="param_1">Значение параметра</label>
                        <div class="worlds_count">
                            <input class="form-control" id="param_1" name="param_1" type="text" value="" maxlength="60">
                            <div class="worlds_counter">
                                <span class="worlds_fill"></span>
                                <span class="worlds_max"></span>
                            </div>
                        </div>
                    </li>
                    <li>
                        <label class="col-form-label" for="param_2">Значение с префиксом</label>
                        <div class="input-group">
                            <span class="input-group-text">prefix</span>
                            <input class="form-control" id="param_2" name="param_2" type="text" value="">
                        </div>
                    </li>
                    <li>
                        <label class="col-form-label" for="param_2">Выпадающий список</label>
                        <select class="form-select" name="show_out_stock" id="show_out_stock">
                            <option value="0">0</option>
                            <option value="1" selected="">1</option>
                            <option value="2">2</option>
                        </select>
                    </li>
                    <li>
                        <label class="col-form-label" for="param_3">Текстовое поле</label>
                        <div class="worlds_count">
                            <textarea class="form-control" id="param_3" name="param_3" maxlength="160"></textarea>
                            <div class="worlds_counter">
                                <span class="worlds_fill"></span>
                                <span class="worlds_max"></span>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="col-12 btn_row">
                    {include file="parts/button.tpl" label="Кнопка"}
                </div>


                <script type="module">
                    import { worldsCount } from '{"js/common.js"|asset}';
                    {literal}
                        $(function() {
                            worldsCount();
                        });
                    {/literal}
                </script>
            </div>
            <!-- End Param Input -->



            <!-- File upload -->
            <div class="col-lg-6 layer">
                <div class="input-group">
                    <input class="form-control import_file" name="file" type="file" value="">
                    <input class="btn btn-primary" type="submit" value="Загрузить">
                </div>
                <p class="mt-2">максимальный размер файла &mdash; {$config->max_upload_filesize|byte_convert}</p>
                <div class="alert alert-info">Хавает CSV, стоимость контейнера не должна быть ниже минимальной цены</div>
            </div>
            <!-- End File upload -->



            <!-- Mini input list -->
            <div class="col-lg-6 layer">
                <h2>Input List</h2>
                <ul class="list mini_list sortable_on">
                    {$param_array = [0 => [id => 1, name => 'name', value => 'value'], 1 => [id => 2,name => 'name_1', value => 'value_1']]}
                    {foreach $param_array as $item}
                        <li class="list_row">
                            <input type="hidden" name="item[{$item@index}][id]" value="{$item.id}">

                            <div class="move">
                                <div class="move_zone"></div>
                            </div>
                            <div class="col">
                                <input class="form-control" name="item[{$item@index}][name]" type="text"
                                    value="{$item.value}" />
                            </div>
                            <div class="icons">
                                <i class="delete material-icons" title="Удалить">cancel</i>
                            </div>
                        </li>
                    {/foreach}


                    <li id="new" class="list_row" style="display:none;">
                        <input type="hidden" name="item[INDEX][id]" value="">
                        <div class="move">
                            <div class="move_zone"></div>
                        </div>
                        <div class="col">
                            <input class="form-control" name="name[INDEX][name]" type="text" value="" />
                        </div>
                        <div class="icons">
                            <i class="delete material-icons" title="Удалить">cancel</i>
                        </div>
                    </li>
                </ul>

                <div class="btn_row_add">
                    <div class="add mt-3">
                        <i class="dash_link">Добавить вариант</i>
                    </div>
                </div>

                <script type="module">
                    {literal}

                        $(function() {

                            // Mini input list
                            const s_variant = $('.mini_list #new').clone(true).removeAttr('id');
                            $('.mini_list #new').removeAttr('id').remove();

                            $('.add').click(function() {
                                s_variant.clone().appendTo('.mini_list').show().find(
                                    'input[name="synonyms[]"]').focus();
                                return false;
                            });

                            // delete input list
                            $(".mini_list").on('click', '.delete', function() {
                                $(this).closest(".list_row").fadeOut(200, function() {
                                    $(this).remove();
                                });
                                return false;
                            });
                        });

                    {/literal}
                </script>
            </div>
            <!-- End Mini input list -->


            <!-- Images -->
            <div id="images" class="col-lg-6 layer images">
                <h2>Images</h2>
                {include file='parts/image_upload_part.tpl' images=$images can_edit=true}
            </div>
            <!-- End Images -->



            <!-- Multiselect Tree-->
            <div class="col-lg-6 layer">
                <h2>Multiselect Tree</h2>
                <select class="form-select multiple_categories" multiple name="pricefeed_categories[]">
                    {function name=category_select selected_id=$product_category level=0}
                        {foreach $categories as $category}
                            <option value="{$category->id}" {if !$category->visible}class="disabled" {/if}
                                {if in_array($category->id, $pricefeed_categories)}selected{/if}
                                category_name="{$category->single_name}">
                                {section name=sp loop=$level}&nbsp;&nbsp;&nbsp;&nbsp;{/section}{$category->name}</option>
                            {category_select categories=$category->subcategories selected_id=$selected_id  level=$level+1}
                        {/foreach}
                    {/function}

                    {category_select categories=$categories}
                </select>
                <div class="col-12 btn_row">
                    {include file="parts/button.tpl" label="Сохранить выбор"}
                </div>
            </div>
            <!-- End Multiselect Trees-->




        </div>
    </form>
{/block}