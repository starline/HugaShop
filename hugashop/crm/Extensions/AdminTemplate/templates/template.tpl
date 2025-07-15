{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

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
                        <div class="col-2">
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                    <div class="list_row">
                        <div class="col">
                            <a href="#">Название второй позиции</a>
                            <div class="notice">Примечание, комментарий, заметки для второй позиции</div>
                        </div>
                        <div class="col-2">
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                    <div class="list_row">
                        <div class="col">
                            <a href="#">Название третьей позиции</a>
                            <div class="notice">Примечание, комментарий, заметки для третьей позиции</div>
                        </div>
                        <div class="col-2">
                            <span class="badge text-bg-round">отметка</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Simple List -->





            <!-- Param Value -->
            <div class="col-lg-6 layer">
                <h2>Заголовок</h2>
                <ul class="property_block">
                    <li>
                        <div class="col-form-label">Размер папки</div>
                        <div class="col-form-label">125 мб</div>
                    </li>
                    <li>
                        <div class="col-form-label">Колличество файлов</div>
                        <div class="col-form-label">10000 шт</div>
                    </li>
                </ul>
                <div class="col-12 btn_row">
                    <button class="btn btn-primary" name="" value="1" type="submit">Кнопка</button>
                </div>
            </div>
            <!-- End Param Value -->





            <!-- Mini input list -->
            <div class="col-lg-6 layer">
                <h2>Input List</h2>
                <ul class="list mini mini_list" id="sort">
                    <li class="list_row">
                        <div class="move">
                            <div class="move_zone"></div>
                        </div>
                        <div class="col">
                            <input class="form-control" name="name[]" type="text" value="value" />
                        </div>
                        <div class="icons">
                            <i class="delete material-icons" title="Удалить">cancel</i>
                        </div>
                    </li>

                    <li id="new" class="list_row" style="display:none;">
                        <div class="move">
                            <div class="move_zone"></div>
                        </div>
                        <div class="col">
                            <input class="form-control" name="name[]" type="text" value="" />
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
                            const s_variant = $('.mini_list #new').clone(true);
                            $('.mini_list #new').remove().removeAttr('id');

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

                            // Dort inpot list
                            $("#sort").sortable({
                                items: ".list_row:not(.sortable_off)",
                                cancel: ".sortable_off",
                                handle: ".move_zone",
                                axis: 'y',
                                opacity: 0.90,
                                tolerance: "pointer"
                            });

                        });

                    {/literal}
                </script>
            </div>
            <!-- End Mini input list -->




            <!-- Images -->
            <div id="images" class="col-lg-6 layer images">
                <h2>Images</h2>
                {include file='parts\image_upload_part.tpl' images=$images can_edit=true}
            </div>
            <!-- End Images -->




        </div>
    </form>
{/block}