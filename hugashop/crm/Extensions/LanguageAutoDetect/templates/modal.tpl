{* LanguageAutoDetect modal *}
<div class="language-auto-detect-modal p-4 text-center">
    <p class="mb-3">{'Switch to %language%?'|trans|replace:'%language%':$match_language->name}</p>
    <button type="button" class="btn btn-secondary me-2 js-current">{$current_language->name}</button>
    <button type="button" class="btn btn-primary js-switch">{$match_language->name}</button>
</div>
<script>
    (function () {
        $('.js-current').on('click', function () {
            $.fancybox.close();
        });
        $('.js-switch').on('click', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('lang', '{$match_language->code}');
            window.location.href = url.toString();
        });
    })();
</script>
