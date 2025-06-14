{extends file='wrapper/main.tpl'}
{include file='extension/parts/menu_part.tpl'}

{$meta_title='Карусель'}

{block name=content}

    <!-- Заголовок -->
    <div class="header_top">
        <h1 class="total_amount">{$meta_title}</h1>
    </div>

    <!-- Основная форма -->
    <form method="post" enctype="multipart/form-data">
        {getCSRFInput}

        <div class="row gx-5">

        </div>
    </form>

{/block}