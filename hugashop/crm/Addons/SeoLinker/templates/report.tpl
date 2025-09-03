{extends 'wrapper/main.tpl'}
{include 'addon/parts/menu_part.tpl'}

{$meta_title='SEO Links'}

{block name=content}
    <div class="header_top">
        {if $pages_total > 0}
            <h1>{$meta_title} <span class="sum_total">{$pages_total}
                    {$pages_total|plural:'страница':'страниц':'страницы'}</span></h1>
        {else}
            <h1>{$meta_title}</h1>
        {/if}
        <button id="scan_button" class="btn btn-primary ms-2">Сканировать</button>
        <span id="scan_count" class="badge text-bg-round ms-2"></span>
    </div>

    <div id="main_list">
        {if $pages}

            <div class="list_navigation">
                {if !$pagination_hide}
                    {include file='parts/pagination.tpl'}
                {elseif ($settings->products_num_admin <= $users_count)}
                    <div class="pagination">Показано только первые {$settings->products_num_admin} покупателей</div>
                {/if}

                <div class="sort">
                    <div class="input-group">
                        <span class="input-group-text">Сортировка</span>
                        <select class="form-select" name="sort">
                            <option value="depth" {if $sort === 'depth'}selected{/if}>По глубине</option>
                            <option value="out_internal" {if $sort === 'out_internal'}selected{/if}>По исходящим внутренним
                            </option>
                            <option value="in_internal" {if $sort === 'in_internal'}selected{/if}>По входящим внутренним
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="list">
                {foreach $pages as $p}
                    <div class="list_row">
                        <div class="col row">
                            <div class="col-12 col-lg-4 text-break">
                                <a href="{'AddonSeoLinkerPage'|link:[id => $p->id]}">{$p->url}</a>
                            </div>
                            <div class="col-3 col-lg-2">
                                <span class="badge text-bg-round" data-bs-toggle="tooltip" title="Глуба страницы">{$p->depth}</span>
                            </div>
                            <div class="col-3 col-lg-2">
                                <span class="badge text-bg-round" data-bs-toggle="tooltip"
                                    title="Исходящие внутрение ссылки">{$p->out_internal}</span>
                            </div>
                            <div class="col-3 col-lg-2">
                                <span class="badge text-bg-round" data-bs-toggle="tooltip"
                                    title="Исходящие внешник ссылки">{$p->out_external}</span>
                            </div>
                            <div class="col-3 col-lg-2 text-end">
                                <span class="badge text-bg-round" data-bs-toggle="tooltip"
                                    title="Входящие внутрение ссылки">{$p->in_internal}</span>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>

            {include file='parts/pagination.tpl'}
        {else}
            Нет ссылок
        {/if}
    </div>
{/block}

{block name=body_script append}
    <script type="module">
        {literal}
            $(function() {
                $('#scan_button').click(function() {
                    let btn = $(this);
                    btn.prop('disabled', true);

                    function iterate(start) {
                        $.ajax({
                            type: 'POST',
                            url: window.location.href,
                            data: {
                                scan: 1,
                                start: start ? 1 : 0,
                                csrf: window.csrf
                            },
                            dataType: 'json',
                            success: function(data) {
                                $('#scan_count').text(data.scanned + '/' + data.pending);

                                if (data.pending > 0) {
                                    iterate(false);
                                } else {
                                    location.reload();
                                }
                            },
                            error: function() {
                                btn.prop('disabled', false);
                            }
                        });
                    }

                    iterate(true);
                    return false;
                });


                // Select sort
                let current_url = new URL(window.location.href);
                $('select[name="sort"]').change(function() {
                    var sort = $(this).val();
                    if (sort != '')
                        current_url.searchParams.set('sort', sort);
                    else
                        current_url.searchParams.delete('sort');

                    current_url.searchParams.delete('page');
                    window.location.href = current_url.toString();
                });
            });
        {/literal}
    </script>
{/block}