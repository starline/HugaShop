<div class="delivery_note">
    <div class="row gx-5">
        <label class="col-form-label col-lg-3" for="delivery_note">Номер накладной</label>
        <div class="col-lg-3">
            <input id="delivery_note" class="form-control" type="text" name="delivery_note"
                value="{$order->delivery_note}" {if !$can_edit}disabled{/if} />
        </div>
    </div>

    {if $order->delivery_note}
        <div class='tracking_status'>
            <input class="" name='delivery[module]' value='{$delivery->module}' type='hidden'>

            <div class="tracking_info">
                <a target='_blank' href='https://www.cdek.ru/ru/tracking?order_id={$order->delivery_note}'>cdek.ru
                    →</a>
            </div>
        </div>
    {/if}

</div>