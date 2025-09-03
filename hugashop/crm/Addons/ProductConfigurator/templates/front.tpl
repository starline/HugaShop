{extends 'wrapper/main.tpl'}

{$meta_title = $configurator->name}

{block name=content}
<form method="post" id="configurator_form">
    {getCSRFInput}
    <div id="configurator_steps">
        {foreach $configurator->steps as $k=>$step}
            <div class="config_step" data-step="{$step->id}" {if $k>0}style="display:none"{/if}>
                <h3>{$step->name}</h3>
                {if $step->image}<img src="{$step->image}" class="img-fluid mb-3" />{/if}
                {foreach $step->options as $opt}
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="option[{$step->id}]" value="{$opt->id}" data-price="{$opt->price}" id="opt{$opt->id}" />
                        <label class="form-check-label" for="opt{$opt->id}">{$opt->name} {if $opt->price>0}<span class="text-muted">+{$opt->price}</span>{/if}</label>
                    </div>
                {/foreach}
                <button type="button" class="btn btn-primary mt-3 next_step">Далее</button>
            </div>
        {/foreach}
        <div id="config_finish" style="display:none">
            <h3>Контактные данные</h3>
            <div class="mb-3"><input class="form-control" name="name" placeholder="Ваше имя" /></div>
            <div class="mb-3"><input class="form-control" name="phone" placeholder="Телефон" /></div>
            <div class="mb-3"><input class="form-control" name="email" placeholder="Email" /></div>
            <p>Итого: <span id="total_price">0</span></p>
            <button type="submit" class="btn btn-success">Отправить</button>
        </div>
    </div>
</form>

{if $submitted}
    <div class="alert alert-success mt-3">Спасибо! Мы свяжемся с вами.</div>
    <p>Итого: {$total}</p>
{/if}
{/block}

{block name=body_script append}
<script type="module">
{literal}
$(function(){
    let total=0;
    $('.next_step').click(function(){
        let stepDiv=$(this).closest('.config_step');
        let checked=stepDiv.find('input:checked');
        if(!checked.length){return;}
        total += parseFloat(checked.data('price')||0);
        stepDiv.hide().next('.config_step').show();
        if(!stepDiv.next('.config_step').length){
            $('#config_finish').show();
            $('#total_price').text(total.toFixed(2));
        }
    });
});
{/literal}
</script>
{/block}
