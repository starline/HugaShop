<!-- LanguageAutoDetect -->
<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let languages = {$languages|json_encode|raw};
    let currentLang = '{$current_language->code}';
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

        if (!match || match.code === currentLang) {
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
                        current: currentLang,
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