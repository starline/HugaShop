{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='SEO Links'}

{block name=content}
    <div class="header_top">
        <h1>{$meta_title}</h1>
        <button id="scan_button" class="btn btn-primary ms-2">Сканировать</button>
        <span id="scan_count" class="badge text-bg-round ms-2"></span>
    </div>
    <div id="main_list">
        {if $pages}
            <div class="list">
                {foreach $pages as $p}
                    <div class="list_row">
                        <div class="row col">
                            <div class="col-12 col-lg-4 text-break">{$p->url}</div>
                            <div class="col-3 col-lg-2"><span class="badge text-bg-round">{$p->depth}</span></div>
                            <div class="col-3 col-lg-2"><span class="badge text-bg-round">{$p->out_internal}</span></div>
                            <div class="col-3 col-lg-2"><span class="badge text-bg-round">{$p->out_external}</span></div>
                            <div class="col-3 col-lg-2 text-end"><span class="badge text-bg-round">{$p->in_internal}</span></div>
                        </div>
                    </div>
                    {if $links_map[$p->url]}
                        <div class="list_row bg-light">
                            <div class="col">
                                <ul class="mb-0 small">
                                    {foreach $links_map[$p->url] as $ln}
                                        <li><a href="{$ln->to_url}" target="_blank">{$ln->to_url}</a> ({$ln->type})</li>
                                    {/foreach}
                                </ul>
                            </div>
                        </div>
                    {/if}
                {/foreach}
            </div>
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
                            data: {scan: 1, start: start ? 1 : 0, csrf: csrf},
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
            });

        {/literal}
    </script>
{/block}