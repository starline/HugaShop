{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{* Meta Title *}
{if $category}
    {$meta_title=$category->name}
{else}
    {$meta_title='Товары'}
{/if}

{block name=content}
    {foreach $languages as $l}
        {if $l->main}
            {assign var=main_language_code value=$l->code}
        {/if}
    {/foreach}

    <div class="two_columns_list">
        <div class="header_top">
            {if $category->name}
                <h1>{$category->name}<span
                        class="sum_total">{$products_count}{$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {else}
                <h1>Все товары <span class="sum_total">{$products_count}
                        {$products_count|plural:'товар':'товаров':'товара'}</span></h1>
            {/if}

            <button class="btn btn-primary ms-2" id="calculate_btn" type="button">
                <span>Посчитать</span>
                <span id="calculate_result" class="badge text-bg-round ms-1"></span>
            </button>

            <form method="get" id="search">
                {getCSRFInput}
                <div class="input-group">
                    <input class="search form-control" type="text" name="keyword" value="{$keyword}"
                        placeholder="Название, артикул" />
                    <input class="input-group-text search_button" type="submit" value="" />
                </div>
            </form>
        </div>

        <div class="navbar-expand-lg" id="right_menu">

            <div class="mb-4">
                <label for="range" class="form-label">Степень заполнености</label>
                <div class="position-relative">
                    <div id="range_tooltip" class="range-tooltip" data-bs-toggle="tooltip" data-bs-placement="top"></div>
                </div>
                <input type="range" class="form-range" min="0" max="100" step="5" id="range" value="{$filling}">

            </div>

            <div class="popup_menu_btn navbar-toggler" data-bs-toggle="offcanvas" data-bs-target="#filter_menu_block">
                <span class="material-icons">menu</span>
                <span class="popup_btn_text">Фильтр</span>
            </div>

            <div class="offcanvas offcanvas-start" id="filter_menu_block" tabindex="-1" aria-labelledby="offcanvasLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>

                <div class="offcanvas-body">
                    {include file='parts/categories_tree_part.tpl'}
                </div>
            </div>
        </div>

        <div id="main_list">
            {if $products}
                {include file='parts/pagination.tpl'}
                <div class="list">
                    {foreach $products as $product}
                        <div class="list_row" item_id="{$product->id}">
                            <div class="image">
                                <img
                                    src="{if $product->image->filename}{$product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>

                            <div class="col row">
                                <div class="col">
                                    <a
                                        href="{'ProductAdmin'|urll:[id=>$product->id]}?return={$smarty.server.REQUEST_URI}">{$product->name}</a>
                                    {if $product->variant_name}
                                        <span class="small"> - {$product->variant_name}</span>
                                    {/if}
                                </div>

                                <div class="col-12 col-md-4">
                                    <div class="row">
                                        <div class="col-6 text-end">
                                            {if $product->sku}
                                                <div class="badge text-bg-round copy_field" value="{$product->sku}">{$product->sku}
                                                    <div class="copy_hover" data-bs-toggle="tooltip"
                                                        data-bs-original-title="Скопировать">
                                                        <i class="material-icons">content_copy</i>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>

                                        <div class="col-6">
                                            {foreach $product->fillings as $lang}
                                                <div class="mb-2 text-end">
                                                    <span
                                                        class="badge {if $lang->percent<20}text-bg-danger{elseif $lang->percent<80}text-bg-warning{else}text-bg-success{/if} ">{$lang->percent}%
                                                        {$lang->language_code}</span>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {assign var=translate_langs value=''}
                            {foreach $product->fillings as $lang}
                                {if $lang->language_code != $main_language_code && $lang->percent < 100}
                                    {assign var=translate_langs value=$translate_langs|cat:$lang->language_code|cat:','}
                                {/if}
                            {/foreach}

                            <div class="icons flex-column">
                                <i class="edit filling material-icons" data-bs-toggle="tooltip" aria-label="Заполнить"
                                    data-bs-original-title="Заполнить">library_books</i>

                                {if $languages|count > 1}
                                    <i class="edit translate material-icons" data-bs-toggle="tooltip" aria-label="Перевести"
                                        data-langs="{$translate_langs|regex_replace:'/,$/':''}"
                                        data-bs-original-title="Перевести">translate</i>
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                </div>
                {include file='parts/pagination.tpl'}
            {/if}
        </div>
    </div>
{/block}


{block name=body_script append}
    <script type="module">
        import '{"js/piecon/piecon.js"|asset}';

        let total = {$products_count};
        let ajax_url = '/admin/extension/{$extension->module}/ajax/calculate';

        {literal}
            $(function() {

                let in_process = false;
                let processed = 0;

                $('#calculate_btn').on('click', function(e) {
                    e.preventDefault();
                    if (in_process) {
                        return;
                    }

                    in_process = true;
                    Piecon.setOptions({ fallback: 'force' });
                    Piecon.setProgress(0);
                    $('#calculate_result').text('0/' + total);
                    do_calculate(processed);
                });

                function do_calculate(from) {
                    $.ajax({
                        url: ajax_url,
                        data: { from: from },
                        dataType: 'json',
                        success: function(data) {
                            processed = data.from;
                            Piecon.setProgress(Math.round(100 * processed / total));
                            $('#calculate_result').text(processed + '/' + total);
                            if (data && !data.end) {
                                do_calculate(processed);
                            } else {
                                Piecon.setProgress(100);
                                in_process = false;
                                location.reload();
                            }
                        }
                    });
                }


                // translate
                $("i.translate.edit").on('click', function() {
                    const icon = $(this);
                    const row = icon.closest('.list_row');
                    const langs = String(icon.data('langs')).split(',').filter(Boolean);
                    if (!langs.length) {
                        return;
                    }
                    const productId = row.attr('item_id');
                    icon.addClass('loading_icon');

                    const translateNext = function(index) {
                        if (index >= langs.length) {
                            location.reload();
                            return;
                        }
                        $.ajax({
                            url: '/admin/extension/OpenAI/ajax/translate',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                entity: 'product',
                                id: productId,
                                lang: langs[index],
                                csrf: csrf
                            },
                            complete: function() {
                                translateNext(index + 1);
                            }
                        });
                    };

                    translateNext(0);
                });


                // filling
                $("i.filling.edit").on('click', function() {
                    const id = $(this).closest('.list_row').attr('item_id');
                    $.ajax({
                        type: 'POST',
                        url: '/admin/extension/OpenAI/ajax/filling',
                        data: {id: id, csrf: csrf},
                        dataType: 'json',
                        success: function(res) {
                            if (res.description) {
                                alert(res.description);
                            }
                        }
                    });
                });


                // Range slader
                const range = document.getElementById('range');
                const tooltipAnchor = document.getElementById('range_tooltip');
                const tooltip = new bootstrap.Tooltip(tooltipAnchor, {
                    title: range.value,
                    trigger: 'manual',
                    placement: 'top'
                });

                function updateTooltip() {
                    const value = Number(range.value);
                    const min = Number(range.min);
                    const max = Number(range.max);
                    const percent = (value - min) * 100 / (max - min);
                    const offset = 10 - percent * 0.2;
                    tooltipAnchor.style.left = `calc(${percent}% + (${offset}px))`;
                    tooltip.setContent({ '.tooltip-inner': String(value) });
                    tooltip.update();
                    tooltip.show();
                }

                range.addEventListener('input', updateTooltip);
                range.addEventListener('change', () => {
                    const url = new URL(window.location.href);
                    url.searchParams.set('filling', range.value);
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                    tooltip.show();
                });

                updateTooltip();
            });
        {/literal}
    </script>
{/block}