<!-- LanguageAutoDetect -->
<script type="module">
    import { asignFancyAjax, getUiLanguage } from "{'js/common.js'|asset}";

    let languages = {$languages|json_encode|raw};
    let current_lang = '{$current_language->code}';
    let storageKey = '{$storage_key}';

    $(function() {

        if (current_lang === localStorage.getItem(storageKey)) {
            return;
        }

        let browserLang = getUiLanguage();
        if (!browserLang) {
            return;
        }

        browserLang = browserLang.split('-')[0];

        let match = languages.find(function(item) {
            return item.code === browserLang;
        });

        if (!match || match.code === current_lang) {
            return;
        }

        $.fancybox.open({
            type: 'ajax',
            src: "{'AddonLanguageAutoDetectSwitcher'|linkLang}",
            ajax: {
                settings: {
                    method: 'POST',
                    data: {
                        match: match.code,
                        current: current_lang,
                        csrf: window.csrf
                    }
                }
            },
            touch: false,
            closeExisting: true,
            afterShow: function() {
                asignFancyAjax();
                localStorage.setItem(storageKey, current_lang);
            }
        });
    });
</script>