<footer>
    <div class="container">

        <!-- Browsed Product -->
        {extension name='ProductBrowsed'}

        {* Выбираем в переменную $last_posts последние записи *}
        {if $last_posts = 'ContentPost'|api:getPosts:[[random => 1, visible => 1, limit => 5]]}
            <div class="my-5">
                <div class="title-wrap">
                    <h3 class="h2">{'Полезная информация'|trans}</h3>
                    <span> → <a href="{'PostList'|linkLang}">{'все статьи'|trans}</a></span>
                </div>
                <div class="posts_content">
                    {foreach $last_posts as $post}
                        <div data-post="{$post->id}">
                            <a href="{'Post'|linkLang:[url => $post->url]}" class="title">{$post->name}</a>
                            <div>{$post->annotation}</div>
                        </div>
                    {/foreach}
                </div>
            </div>
        {/if}


        <div class="row py-5 border-top">
            <div class="col-lg-3 my-3">
                <div class="h5">{$settings->company_name}</div>
                <div>© 2016-{$now|date:Y}</div>

                <div class="soc_info notranslate my-4 text-center">
                    <a class="insta" href="https://www.instagram.com/grizlicnc/" target="blank"
                        rel="nofollow">Instagram</a>
                    <a class="fb" href="https://www.facebook.com/grizlicnc/" target="blank" rel="nofollow">Facebook</a>
                    <a class="youtube" href="https://www.youtube.com/@grizlicnc" target="blank"
                        rel="nofollow">YouTube</a>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="row">
                    <div class="col-lg-4 my-3">
                        <div class="h5">{'Каталог'|trans}</div>

                        {foreach 'ProductCategory'|api:getCategories:[[main => 1, visible => 1, level => 3]] as $cat}
                            <div class="my-2">
                                <a href="{'Products'|linkLang:[url=>$cat->url]}" class="link-secondary">{$cat->name}</a>
                            </div>
                        {/foreach}
                    </div>
                    <div class="col-lg-4 my-3">
                        <div class="h5">{'Клиентам'|trans}</div>

                        <div class="my-2">
                            <a href="{'User'|linkLang}" rel="nofollow"
                                class="link-secondary">{'Вход в кабинет'|trans}</a>
                        </div>
                        <div class="my-2">
                            <a href="{'PostList'|linkLang}" class="link-secondary">{'База знаний'|trans}</a>
                        </div>

                        {foreach 'ContentPage'|api:getMenu as $m}
                            <div class="my-2">
                                <a href="{'Page'|linkLang:[url => $m->url]}" class="link-secondary">{$m->name}</a>
                            </div>
                        {/foreach}
                    </div>
                    <div class="col-lg-4 my-3">
                        <div class="h5">{'Контактная информация'|trans}</div>
                        <div class="my-2">
                            <a href="tel:+380960441916" class="link-secondary">+38 (096) 044 19 16</a><span
                                class="ms-2">Viber</span>
                        </div>
                        <div class="my-2">
                            <a href="tel:+380930441916" class="link-secondary">+38 (093) 044 19 16</a>
                        </div>
                        <div>
                            <a href="tg:grizlicnc_ua" class="link-secondary">@grizlicnc_ua</a><span
                                class="ms-2">Telegram</span>
                        </div>
                        <div class="my-2">
                            <a href="mailto:grizlicnc@gmail.com" class="link-secondary">grizlicnc@gmail.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>