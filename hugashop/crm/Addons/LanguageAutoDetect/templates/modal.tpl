{block name=content}
    <div>
        <div class="language-auto-detect-modal p-4 text-center">
            <p class="mb-3">{'Switch to'|trans} {$match_language->name}</p>
            <button type="button" class="btn btn-secondary me-2 js-current">{$current_language->name}</button>
            <button type="button" class="btn btn-primary js-switch">{$match_language->name}</button>
        </div>

        <script type="module">
            let storageKey = '{$storage_key}';

            $(function() {
                $('.js-current').on('click', function() {
                    $.fancybox.close();
                });

                $('.js-switch').on('click', function() {
                    localStorage.setItem(storageKey, '{$match_language->code}');

                    let path = window.location.pathname;
                    let langReg = /^\/([a-z]{2})(\/|$)/;

                    if (langReg.test(path)) {
                        path = path.replace(langReg, '/{$match_language->code}$2');
                    } else {
                        path = '/{$match_language->code}' + path;
                    }

                    window.location.href = window.location.origin + path + window.location.search + window
                        .location.hash;
                });
            });
        </script>
    </div>
{/block}