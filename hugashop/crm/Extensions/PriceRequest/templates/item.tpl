{extends 'wrapper/main.tpl'}
{include 'extension/parts/menu_part.tpl'}

{block name=content}
    <div class="header_top">
        <h1>Запрос #{$request->id}</h1>
    </div>
    <div class="row gx-5">
        <div class="col-lg-6">
            <ul class="property_block">
                <li><b>ФИО:</b> {$request->name}</li>
                <li><b>Телефон:</b> {$request->phone}</li>
                <li><b>Email:</b> {$request->email}</li>
                <li><b>Ссылка:</b> <a href="{$request->link}" target="_blank">{$request->link}</a></li>
                <li><b>IP:</b> {$request->ip}</li>
                <li><b>Дата:</b> {$request->created_at|date} {$request->created_at|time}</li>
            </ul>
        </div>
        <div class="col-lg-6">
            {if $request->product}
                <h2>Товар</h2>
                <a href="{'ProductAdmin'|link:[id => $request->product->id]}">{$request->product->name}</a>
                {if $request->product->image}
                    <div class="mt-2">
                        <img src="{$request->product->image->filename|resize:200:200}" class="img-thumbnail" alt="" />
                    </div>
                {/if}
            {/if}
        </div>
    </div>
{/block}

