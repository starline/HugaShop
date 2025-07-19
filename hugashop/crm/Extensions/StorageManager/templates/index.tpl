{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{$meta_title='Хранилище'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1 class="total_amount">{$meta_title}
            <div class="currency_amount">
                <span class="sum_total">{$total->files} <span class="sum_profit_price">
                        {$total->files|plural:'файл':'файлов':'файла'}</span>
                </span>
            </div>
            <div class="currency_amount">
                <span class="sum_total">{$total->size|byte_convert:value} <span class="sum_profit_price">
                        {$total->size|byte_convert:unit}</span>
                </span>
            </div>
        </h1>
    </div>


    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        {getCSRFInput}

        <div class="row gx-5">

            {foreach $storages as $folder_name=>$folder_params}
                <div class="col-lg-6 layer">
                    <h2>Папка {$folder_name}</h2>
                    <ul class="property_block">
                        <li>
                            <div class="col-form-label">Размер папки</div>
                            <div class="col-form-label">{$folder_params->size|byte_convert}</div>
                        </li>
                        <li>
                            <div class="col-form-label">Количество файлов</div>
                            <div class="col-form-label">{$folder_params->files}</div>
                        </li>
                    </ul>

                    {if $folder_params->clear}
                        <div class="col-12 btn_row">
                            <button class="btn btn-primary" name="{$folder_name}" value="1" type="submit">Очистить</button>
                        </div>
                    {/if}
                </div>
            {/foreach}
        </div>
    </form>
{/block}