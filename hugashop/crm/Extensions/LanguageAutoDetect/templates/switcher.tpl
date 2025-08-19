<!-- LanguageAutoDetect -->
<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let languages = {$languages|json_encode|raw};
    let current_lang = '{$current_language->code}';
    let storageKey = '{$storage_key}';

    $(function() {

        if (localStorage.getItem(storageKey)) {
            //return;
        }

        let browserLang = 'uk-Ru'; //navigator.language || navigator.userLanguage;
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
            src: "{'ExtLanguageAutoDetectSwitcher'|linkLang}",
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
            afterClose: function() {
                localStorage.setItem(storageKey, 1);
                asignFancyAjax();
            }
        });
    });
</script>