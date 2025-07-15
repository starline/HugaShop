<div class="list_row {if $mailing->send}highlight{/if}" item_id="{$mailing->id}">
    <div class="checkbox">
        <input class="form-check-input" type="checkbox" name="check[]" value="{$mailing->id}" />
    </div>

    <div class="order_date">
        <div class="date">{$mailing->create_date|date}</div>
        <div class="time">{$mailing->create_date|time}</div>
    </div>

    <div class="col row">
        <div class="col-12 col-sm-5">
            <a href="{'MailingAdmin'|urll:[id => $mailing->id]}">{$mailing->contact}</a>
            {if !$mailing->user->id|empty}
                <a href="{'UserAdmin'|urll:[id => $mailing->user->id]}">{$mailing->user->name}</a>
            {/if}
        </div>

        <div class="col-4 col-sm-1">
            <div class="badge text-bg-round">
                {$mailing->count}
            </div>
        </div>

        <div class="col-8 col-sm-4">
            <span class="badge text-bg-round">{$mailing->notifier->type}</span>
            <span class="badge text-bg-round">{$mailing->notifier->name}</span>
        </div>

        <div class="col-12 col-sm-2">
            <div class="mail_status {if $mailing->send}send{elseif $mailing->frozen}frozen{/if}">
                {if $mailing->send}отправлено{elseif $mailing->frozen}заморожено{else}в очереди{/if}
            </div>
        </div>
    </div>

    <div class="icons">
        <i class="delete material-icons" data-bs-toggle="tooltip" title="Удалить">cancel</i>
    </div>
</div>