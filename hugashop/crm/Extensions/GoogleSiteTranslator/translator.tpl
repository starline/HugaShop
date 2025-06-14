<link rel="stylesheet" href="{'GoogleSiteTranslator/translator.css'|asset:'extensions'}" />

<script type="text/javascript" src="{'GoogleSiteTranslator/js.cookie.min.js'|asset:'extensions'}"></script>
<script type="text/javascript" src="{'GoogleSiteTranslator/translator.js'|asset:'extensions'}"></script>

<!-- GoogleSiteTranslator -->
<script>
    let first_language = "{$GoogleSiteTranslator->first_code}";
    let auto_detect = "{$GoogleSiteTranslator->auto_detect}";

    let available_languages = "{$GoogleSiteTranslator->lang_codes}";
    if (available_languages)
        available_languages = available_languages.split(",");

    // Auto detect languages
    let lang = navigator.language || navigator.userLanguage;
    if (lang && auto_detect === "1") {
        lang = lang.split("-")[0]; // uk-UA
        if (available_languages.includes(lang))
            first_language = lang;
    }

    const googleTranslateConfig = {
        lang: "{$GoogleSiteTranslator->main_code}",  // Original language
        langFirstVisit: first_language // The language we translate into on the first visit
    };

    GoogleSiteTranslatorStart();
</script>

{if $GoogleSiteTranslator->use_own_template != 1}
    <div class="language">
        <img src="{'GoogleSiteTranslator/lang__uk.png'|asset:'extensions'}" alt="UA" title="Українська мова"
            data-google-lang="uk" class="language__img">
        <img src="{'GoogleSiteTranslator/lang__ru.png'|asset:'extensions'}" alt="RU" title="Руский язык"
            data-google-lang="ru" class="language__img">
    </div>
{/if}