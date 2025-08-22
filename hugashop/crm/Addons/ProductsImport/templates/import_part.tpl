{foreach $items as $item}
    <li>
        {if !$item->error}
            <span class="count">{$num--}</span>
            <span class="status added"></span>
            <span class="badge text-bg-round mx-2 copy_field" value="{$item->product->sku}">
                <span>{$item->product->sku}</span>
                <div class="copy_hover" data-bs-toggle="tooltip" data-bs-original-title="Скопировать">
                    <i class="material-icons">content_copy</i>
                </div>
            </span>
            <a target="_blank" href="{'ProductPriceAdmin'|link:[id => $item->product->id]}">{$item->product->name}</a>
            <span class="ms-2">{$item->amount} шт.</span>
        {else}
            {$item->error}
        {/if}
    </li>
{/foreach}