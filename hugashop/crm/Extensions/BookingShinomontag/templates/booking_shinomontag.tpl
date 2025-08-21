{extends 'wrapper/main.tpl'}

{$meta_title = 'Запись на шиномонтаж'}

{block name=content}

        <!-- Breadcrumbs -->
        <div id="path">
                <ul itemscope itemtype="https://schema.org/BreadcrumbList">
                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                <a href="{'Main'|linkLang}" itemprop="item">
                                        <span itemprop="name">{'Главная'|trans}</span>
                                        <meta itemprop="position" content="1" />
                                </a>
                        </li>
                </ul>
        </div>

        <h1>Запись на шиномонтаж</h1>

        {if $booking_sent}
                <div class="alert alert-info">
                        {$name}, ваша заявка отправлена.
                </div>
        {else}
                <form class="form" method="post">
                        {getCSRFInput}
                        <div class="row g-4">
                                <div class="col-lg-4">
                                        <label class="form-label" for="date">Дата</label>
                                        <input class="form-control {if date|in_array:$form_invalid}is-invalid{/if}" value="{$date}" name="date" type="date" id="date" />
                                        <div class="invalid-feedback">Выберите дату</div>
                                </div>
                                <div class="col-lg-4">
                                        <label class="form-label" for="time">Время</label>
                                        <input class="form-control {if time|in_array:$form_invalid}is-invalid{/if}" value="{$time}" name="time" type="time" id="time" />
                                        <div class="invalid-feedback">Выберите время</div>
                                </div>
                                <div class="col-lg-4">
                                        <label class="form-label" for="name">Имя</label>
                                        <input class="form-control {if name|in_array:$form_invalid}is-invalid{/if}" value="{$name}" name="name" maxlength="255" type="text" id="name" placeholder="Имя" />
                                        <div class="invalid-feedback">Введите имя</div>
                                </div>
                                <div class="col-lg-4">
                                        <label class="form-label" for="phone">Телефон</label>
                                        <input class="form-control {if phone|in_array:$form_invalid}is-invalid{/if}" value="{$phone}" name="phone" maxlength="32" type="text" id="phone" placeholder="Телефон" />
                                        <div class="invalid-feedback">Введите телефон</div>
                                </div>
                                <div class="col-12">
                                        <textarea class="form-control" name="comment" placeholder="Комментарий">{$comment}</textarea>
                                </div>
                                <div class="col-12">
                                        <button class="btn btn-light" type="submit" value="true">Отправить</button>
                                </div>
                        </div>
                </form>
        {/if}
{/block}
