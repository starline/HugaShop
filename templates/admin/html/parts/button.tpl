<button class="btn {$class|default:'btn-primary'}" type="{$type|default:'submit'}" {if $id}id="{$id}" {/if}
    {$extra_attrs|default:''}>
    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
    <span class="btn-content">{$label|default:'Сохранить'}</span>
</button>