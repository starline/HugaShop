{extends 'wrapper/main.tpl'}

{block name=content}
    <div class="my-5 text-center">
        <h1>{'Страница не найдена'|trans}</h1>
        <p class="text-muted">{'Извините, но запрашиваемая страница не существует.'|trans}</p>
        <a href="{'Main'|linkLang}" class="btn btn-outline-primary mt-3">{'На главную'|trans}</a>
    </div>
{/block}

