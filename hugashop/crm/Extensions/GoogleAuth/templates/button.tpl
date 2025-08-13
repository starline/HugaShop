{$authUrl = 'ExtGoogleAuth'|linkLang}
<div class="w-100 my-3">
    <a class="btn btn-outline-danger w-100" href="{$authUrl}"
        onclick="window.open('{$authUrl}?popup=1', 'googleauth', 'width=500, height=600');return false;">{'Войти через Google'|trans}</a>
</div>