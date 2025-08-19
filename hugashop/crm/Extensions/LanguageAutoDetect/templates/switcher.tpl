{* LanguageAutoDetect script *}
<script>
    (function () {
        var languages = {$LanguageAutoDetect_languages|json_encode|raw};
        var currentLang = '{$current_language->code}';
        var storageKey = 'language_auto_detect_seen';

        if (localStorage.getItem(storageKey)) {
            return;
        }

        var browserLang = navigator.language || navigator.userLanguage;
        if (!browserLang) {
            return;
        }
        browserLang = browserLang.split('-')[0];

        var match = languages.find(function (item) {
            return item.code === browserLang;
        });

        if (!match || match.code === currentLang) {
            return;
        }

        $.fancybox.open({
            type: 'ajax',
            src: "{'ExtLanguageAutoDetectSwitcher'|linkLang|escape:'javascript'}" + '?match=' + match.code + '&current=' + currentLang,
            touch: false,
            closeExisting: true,
            afterClose: function () {
                localStorage.setItem(storageKey, 1);
                asignFancyAjax();
            }
        });
    })();
</script>
