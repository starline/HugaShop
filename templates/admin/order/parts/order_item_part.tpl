<div class="list_row {if $order->paid}highlight{/if}" item_id="{$order->id}">

    {if 'order_edit'|user_access and ($status|in_array:[2, 3, 4] || !$keyword|empty)}
        <div class="checkbox">
            <input class="form-check-input" type="checkbox" name="check[]" value="{$order->id}" />
        </div>
    {/if}

    <div class="order_date">
        <a class="order_id" href="{'OrderAdmin'|urll:[id => $order->id]}">#<span>{$order->id}</span></a>
        <div class="date">{$order->date|date}</div>
        <div class="time">{$order->date|time}</div>
    </div>

    <div class="col">
        <div class="row">
            <div class="col-12 col-md-5">

                <a href="{'OrderAdmin'|urll:[id => $order->id]}"><b>{$order->name}</b></a>

                {if $order->purchases}
                    <div class="purchases">
                        {foreach $order->purchases as $purchase}
                            <div class="image">
                                <div class="amount">{$purchase->amount}</div>
                                <img data-bs-toggle="tooltip"
                                    title="{$purchase->product->name} {if $purchase->variant_name} - {$purchase->variant_name}{/if}"
                                    src="{if $purchase->product->image->filename}{$purchase->product->image->filename|resize:60}{else}{'images/cargo.png'|asset}{/if}" />
                            </div>
                        {/foreach}
                    </div>
                {/if}
            </div>

            <div class="col-12 col-md-5 mt-3 mt-md-0">
                <div class="order_price">
                    {$order->payment_price|price_html|raw}

                    {if $order->status == 3}
                        <span class="order_decline rounded">отменен</span>
                    {/if}

                    {if $order->paid}
                        <span class="order_paid rounded">оплачен</span>
                    {/if}

                    {if 'order_finance'|user_access}
                        <span class="profit_price">{$order->profit_price|price_html:profit|raw}</span>
                    {/if}
                </div>

                {if $order->payment_method->name}
                    <div class="round_box mt-1">{$order->payment_method->name}</div>
                {/if}

                {if $order->delivery_method->name}
                    <div class="round_box mt-1">{$order->delivery_method->name}</div>
                {/if}

                <div class="order_phone">
                    {$order->phone}
                </div>

                <div class="order_address">
                    {$order->address}
                </div>

                <div class="notice">
                    {$order->comment|strip_tags|nl2br|raw}
                </div>

                {if $order->note}
                    <div class="notice_block">
                        <div class="notice_block_text">{$order->note|strip_tags|nl2br|raw}</div>
                        <div class="show_link_block">
                            <a class="show_link" href="#">раскрыть ↓</a>
                        </div>
                    </div>
                {/if}
            </div>

            <div class="col-12 col-md-2 text-center">

                {if $order->labels}
                    {foreach $order->labels as $lab}
                        <span class="order_label_text" style="background-color:#{$lab->color};">{$lab->name}</span>
                    {/foreach}
                {/if}

                {if !$status}
                    {if $order->status == 0}
                        <img src="{'images/new.png'|asset}" data-bs-toggle="tooltip" title='Новый'>
                    {elseif $order->status == 1}
                        <img src="{'images/time.png'|asset}" data-bs-toggle="tooltip" title='Принят'>
                    {elseif $order->status == 4}
                        <img src="{'images/time.png'|asset}" data-bs-toggle="tooltip" title='Отгружен'>
                    {elseif $order->status == 2}
                        <img src="{'images/tick.png'|asset}" data-bs-toggle="tooltip" title='Выполнен'>
                    {/if}
                {/if}
            </div>
        </div>
    </div>

    {if 'order_delete'|user_access AND ($status == 3 || !$keyword|empty)}
        <div class="icons">
            <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
        </div>
    {/if}
</div>