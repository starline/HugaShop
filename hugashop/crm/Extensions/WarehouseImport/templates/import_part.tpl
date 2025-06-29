{foreach $items as $item}
    <li>
        {if !$item->error}
            <span class="count">{$num--}</span>
            <span class="status added"></span>
            <span class="badge text-bg-round mx-2">{$item->product->sku}</span>
            <a target="_blank" href="/admin/product/{$item->product->id}/price">{$item->product->name}</a>
            <span class="ms-2">{$item->amount} шт.</span>
        {else}
            {$item->error}
        {/if}
    </li>
{/foreach}