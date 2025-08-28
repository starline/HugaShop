{block name=content}
    <div class="container">

        {include 'parts/translation_btn_part.tpl' entity='product'}

        <form method="post" action="{'AddonProductFillingContent'|linkLang:[id => $product->id]}"
            enctype="multipart/form-data">
            <input name="id" type="hidden" value="{$product->id}" />
            {getCSRFInput}

            <div class="row gx-5">
                <div class="col-12">
                    <div class="over_name">
                        <div class="checkbox_line"></div>
                        <div class="link_line">
                            <a class="out_link" target="_self" href="{'Product'|linkLang:[url => $product->url]}">Открыть
                                товар на сайте</a>
                        </div>
                    </div>

                    <div class="name_row">
                        <div class="col">
                            <input class="form-control form-control-lg {if name|in_array:$form_invalid}is-invalid{/if}"
                                name="name" type="text" value="{$product->name}" autocomplete="off"
                                placeholder="Название товара" />
                            <div class="invalid-feedback">Введите название товара</div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 layer">
                    <h2>Параметры страницы (мета-теги)</h2>
                    <ul class="property_block">
                        <li>
                            <label for="url" class="col-form-label">Адрес (url)</label>
                            <div class="input-group">
                                <span class="input-group-text">tovar-</span>
                                <input class="form-control" id="url" name="url" type="text" value="{$product->url}" />
                            </div>
                        </li>
                        <li>
                            <label for="meta_title" class="col-form-label">Заголовок (MetaTitle)</label>
                            <div class="worlds_count">
                                <input class="form-control" id="meta_title" name="meta_title" type="text" maxlength="60"
                                    value="{$product->meta_title}" />
                                <div class="worlds_counter">
                                    <span class="worlds_fill"></span>
                                    <span class="worlds_max"></span>
                                </div>
                            </div>
                        </li>
                        <li>
                            <label for="meta_description" class="col-form-label">Описание (MetaDescription)
                                <div class="emojis">{$settings->emojis}</div>
                            </label>
                            <div class="worlds_count">
                                <textarea class="form-control" id="meta_description" maxlength="160"
                                    name="meta_description">{$product->meta_description}</textarea>
                                <div class="worlds_counter">
                                    <span class="worlds_fill"></span>
                                    <span class="worlds_max"></span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="col-12 layer">
                    <h2>Полное описание</h2>
                    <textarea id="body" name="body" class="html_editor editor_large">{$product->body}</textarea>
                </div>

                <div class="col-12 btn_row">
                    {include file="parts/button.tpl"}
                </div>

            </div>
        </form>
    </div>
{/block}

{block name=body_script append}
    {include file='parts/tinymce_init.tpl'}
{/block}