{assign var=authUrl value='ExtGoogleAuth'|linkLang}
<div class="border-top w-100 mt-5">
    <div class="my-5">
        <a class="btn btn-outline-danger w-100" href="{$authUrl}"
            onclick="popupGoogle(event);">{'Войти через Google'|trans}</a>
    </div>


    <script>
        const authUrl = '{$authUrl}';

        {literal}
            function popupGoogle(e) {

                // Мобилки — обычный редирект без попапа
                if (window.innerWidth < 768) return true;

                if (e && e.preventDefault) e.preventDefault();

                let w = 500,
                    h = 600;
                let l = Math.max((window.screen.width - w) / 2, 0);
                let t = Math.max((window.screen.height - h) / 2, 0);

                // Пытаемся открыть попап
                let win = window.open(
                    authUrl + '?popup=1',
                    'googleauth',
                    'width=' + w + ',height=' + h + ',left=' + l + ',top=' + t + ',resizable=yes,scrollbars=yes'
                );

                // Если попап заблокирован — делаем обычный переход
                if (!win) {
                    window.location.href = authUrl;
                    return false;
                }

                return false;
            }
        {/literal}
    </script>
</div>