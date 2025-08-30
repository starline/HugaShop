<ul id="main_menu" class="ps-2">


    {if 'order'|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/orders.png'|asset}" />
                </div>
                <b>CRM</b>
            </span>

            <ul>
                <li class="mini {if $route|in_array:[OrderListAdmin, OrderAdmin, OrderNewAdmin]}active{/if}">
                    <a href="{'OrderListAdmin'|linkLang:[status => 0]}">{'Orders'|trans}</a>
                    {if $orders_info_count[0]}
                        <div class="badge rounded-pill bg-danger">
                            {$orders_info_count[0]}</div>
                    {/if}
                </li>

                <li class="mini {if $route|in_array:['CartListAdmin']}active{/if}">
                    <a href="{'CartListAdmin'|linkLang}">{'Carts'|trans}</a>
                </li>

                {if 'user_manager'|user_access}
                    <li class="mini right {if $route|in_array:[ManagerProfitAdmin]}active{/if}">
                        <a href="{'ManagerProfitAdmin'|linkLang}">Доход менеджера</a>
                    </li>
                {/if}

                {if 'order_payment'|user_access}
                    <li
                        class="mini {if $route|in_array:[OrderPaymentListAdmin, OrderPaymentAdmin, OrderPaymentNewAdmin]}active{/if}">
                        <a href="{'OrderPaymentListAdmin'|linkLang}">Оплата</a>
                    </li>
                {/if}

                {if 'order_delivery'|user_access}
                    <li
                        class="mini {if $route|in_array:[DeliveryListAdmin, OrderDeliveryAdmin, OrderDeliveryNewAdmin]}active{/if}">
                        <a href="{'DeliveryListAdmin'|linkLang}">Доставка</a>
                    </li>
                {/if}

                {if 'order_label'|user_access}
                    <li class="mini {if $route|in_array:[LabelListAdmin, LabelAdmin, LabelNewAdmin]}active{/if}">
                        <a href="{'LabelListAdmin'|linkLang}">Метки</a>
                    </li>
                {/if}
                {foreach $addons_menu.crm|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if ['product_view', 'warehouse']|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/catalog.png'|asset}" />
                </div>
                <b>Склад</b>
            </span>

            <ul>
                {if 'product_view'|user_access}
                    <li
                        class="mini {if $route|in_array:[ProductListAdmin, ProductAdmin, ProductPriceAdmin, ImportProductPAdmin]}active{/if}">
                        <a href="{'ProductListAdmin'|linkLang}">Товары</a>
                    </li>
                {/if}

                {if 'warehouse'|user_access}
                    <li class="mini {if $route|in_array:['MoveAdmin','MoveListAdmin']}active{/if}">
                        <a href="{'MoveListAdmin'|linkLang}">Поставки</a>
                    </li>
                {/if}
                {foreach $addons_menu.warehouse|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if ['user', 'user_coupon', 'user_notifier']|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/users.png'|asset}">
                </div>
                <b>Клиенты</b>
            </span>

            <ul>
                {if 'user'|user_access}
                    <li class="mini {if $route|in_array:[UserListAdmin, UserAdmin, UserSettingsAdmin]} active{/if}">
                        <a href="{'UserListAdmin'|linkLang}">Покупатели</a>
                    </li>
                {/if}

                {if 'user_notifier'|user_access}
                    <li
                        class="mini {if $route|in_array:[MailingNewAdmin, MailingAdmin, MailingListAdmin, NotifierAdmin, NotifierListAdmin, NotifierNewAdmin, MailTemplateNewAdmin, MailTemplateListAdmin, MailTemplateAdmin]}active{/if}">
                        <a href="{'MailingListAdmin'|linkLang}">Список рассылки</a>
                    </li>
                {/if}

                {if 'user_coupon'|user_access}
                    <li class="right mini {if $route|in_array:[CouponListAdmin, CouponAdmin, CouponNewAdmin]}active{/if}">
                        <a href="{'CouponListAdmin'|linkLang}">Купоны</a>
                    </li>
                {/if}
                {foreach $addons_menu.clients|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if ['blog', 'comment', 'feedback', 'page']|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/pages.png'|asset}">
                </div>
                <b>Контент</b>
            </span>

            <ul>
                {if 'blog'|user_access}
                    <li class="mini {if $route|in_array:[PostAdmin, PostListAdmin, PostNewAdmin]}active{/if}">
                        <a href="{'PostListAdmin'|linkLang}">Блог</a>
                    </li>
                {/if}

                {if 'comment'|user_access}
                    <li class="mini {if $route|in_array:[CommentListAdmin, CommentAdmin]}active{/if}">
                        <a href="{'CommentListAdmin'|linkLang}">Комментарии</a>
                        {if $new_comments_counter}
                            <div class="badge rounded-pill bg-danger">
                                {$new_comments_counter}
                            </div>
                        {/if}
                    </li>
                {/if}


                {if 'feedback'|user_access}
                    <li class="mini right {if $route|in_array:[FeedbackListAdmin]}active{/if}">
                        <a href="{'FeedbackListAdmin'|linkLang}">Обратная связь</a>
                    </li>
                {/if}

                {if 'page'|user_access}
                    <li class="mini right  {if $route|in_array:[PageListAdmin, PageAdmin]}active{/if}">
                        <a href="{'PageListAdmin'|linkLang}">Страницы</a>
                    </li>
                {/if}
                {foreach $addons_menu.content|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if ['finance', 'stats']|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/finance.png'|asset}">
                </div>
                <b>Финансы</b>
            </span>

            <ul>
                {if 'finance'|user_access}
                    <li class="mini {if $route|in_array:[PaymentListAdmin, PaymentAdmin, PaymentNewAdmin]}active{/if}">
                        <a href="{'PaymentListAdmin'|linkLang}">Платежи</a>
                    </li>
                {/if}
                {if 'stats'|user_access}
                    <li class="mini {if $route|in_array:[StatsAdmin]}active{/if}">
                        <a href="{'StatsAdmin'|linkLang}">Статистика продаж</a>
                    </li>
                {/if}
                {if 'finance'|user_access}
                    <li class="mini {if $route|in_array:[CurrencyAdmin]}active{/if}">
                        <a href="{'CurrencyAdmin'|linkLang}">Валюты</a>
                    </li>
                {/if}
                {foreach $addons_menu.finance|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if 'addon'|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/wizards.png'|asset}">
                </div>
                <b>Модули</b>
            </span>

            <ul>
                <li class="mini {if $route == 'AddonListAdmin'}active{/if}">
                    <a href="{'AddonListAdmin'|linkLang}">Список модулей</a>
                </li>
                {foreach $addons_menu.addon|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}


    {if ['settings', 'backup', 'design']|user_access}
        <li>
            <span>
                <div class="menu_icon">
                    <img loading="lazy" src="{'images/menu/settings.png'|asset}">
                </div>
                <b>Настройки</b>
            </span>

            <ul>
                {if 'settings'|user_access}
                    <li class="mini {if $route == 'SettingsAdmin'}active{/if}">
                        <a href="{'SettingsAdmin'|linkLang}">Основные настройки</a>
                    </li>
                {/if}

                {if 'settings'|user_access}
                    <li class="mini {if $route == 'LanguageListAdmin'}active{/if}">
                        <a href="{'LanguageListAdmin'|linkLang}">Языки</a>
                    </li>
                {/if}

                {if 'backup'|user_access}
                    <li class="{if $route == 'BackupAdmin'}active{/if}">
                        <a href="{'BackupAdmin'|linkLang}">Бекап</a>
                    </li>
                {/if}

                {if 'design'|user_access}
                    <li
                        class="{if $route|in_array:[ImagesAdmin, ThemeAdmin, StylesAdmin, TemplatesAdmin, ThemeAdmin]}active{/if}">
                        <a href="{'ThemeAdmin'|linkLang}">Тема</a>
                    </li>
                {/if}
                {foreach $addons_menu.settings|default:[] as $ext_module}
                    <li class="mini {if isset($addon) and $addon->module == $ext_module->module}active{/if}">
                        <a href="{'AddonAdmin'|linkLang:[name => $ext_module->module]}">{$ext_module->name}</a>
                    </li>
                {/foreach}
            </ul>
        </li>
    {/if}
</ul>