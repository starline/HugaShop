/*!***************************************************
 * google-translate.js v1.0.7
 * https://Get-Web.Site/
 * author: Vitalii P.
 * author: Andri Huga
 *****************************************************/

//const googleTranslateConfig = {
/* Original language */
//lang: "ru",

/* Если хотите подписаться на событие "FinishTranslate" (Момент когда скрипт закончил перевод), расскоментируйте и добавьте любое проверочное слово на оригинальном языке */
/* If you want to subscribe to the "FinishTranslate" event (The moment when the script finished translating), uncomment and add any test word in the original language */
// testWord: "Язык",

/* The language we translate into on the first visit*/
/* Язык, на который переводим при первом посещении */
// langFirstVisit: 'en',

//};

function GoogleSiteTranslatorStart() {
    document.addEventListener("DOMContentLoaded", () => {

        /* Connecting the google translate widget */
        let script = document.createElement("script");
        script.src = `//translate.google.com/translate_a/element.js?cb=TranslateWidgetIsLoaded`;
        document.getElementsByTagName("head")[0].appendChild(script);
    });
}

function TranslateWidgetIsLoaded() {
    TranslateInit(googleTranslateConfig);
}

function TranslateInit(config) {

    if (config.langFirstVisit && !Cookies.get("googtrans")) {

        /* If the translation language is installed for the first visit and cookies are not assigned */
        TranslateCookieHandler("/auto/" + config.langFirstVisit);
    }

    let code = TranslateGetCode(config);

    TranslateHtmlHandler(code);

    if (code == config.lang) {
        /* If the default language is the same as the language we are translating into, then we clear the cookies */
        TranslateCookieHandler(null);
    }

    if (config.testWord) TranslateMutationObserver(config.testWord, code == config.lang);

    /* Initialize the widget with the default language */
    new google.translate.TranslateElement({
        pageLanguage: config.lang,
        multilanguagePage: true, // Your page contains content in more than one languages
    });

    /* Assigning a handler to the flags */
    TranslateEventHandler("click", "[data-google-lang]", function (e) {
        TranslateCookieHandler(
            "/" + config.lang + "/" + e.getAttribute("data-google-lang")
        );

        /* Reloading the page */
        window.location.reload();
    });
}

function TranslateGetCode(config) {

    /* If there are no cookies, then we pass the default language */
    let lang =
        Cookies.get("googtrans") != undefined && Cookies.get("googtrans") != "null"
            ? Cookies.get("googtrans")
            : config.lang;
    return lang.match(/(?!^\/)[^\/]*$/gm)[0];
}

function TranslateCookieHandler(val) {

    /* Writing down cookies */
    Cookies.set("googtrans", val, { path: "/" });
}

function TranslateEventHandler(event, selector, handler) {
    document.addEventListener(event, function (e) {
        let el = e.target.closest(selector);
        if (el) handler(el);
    });
}

function TranslateHtmlHandler(code) {

    /* We get the language to which we translate and produce the necessary manipulations with DOM */
    if (document.querySelector('[data-google-lang="' + code + '"]') !== null) {
        document
            .querySelector('[data-google-lang="' + code + '"]')
            .classList.add("language__img_active");
    }
}

function TranslateMutationObserver(word, isOrigin) {

    if (isOrigin) {
        document.dispatchEvent(new CustomEvent("FinishTranslate"));
    } else {

        /* Creating a hidden block in which we add a test word in the original language. This will allow us to track the moment when the site is translated and trigger the "FinishTranslate" event  */

        let div = document.createElement('div');
        div.id = 'googleTranslateTestWord';
        div.innerHTML = word;
        div.style.display = 'none';
        document.body.prepend(div);

        let observer = new MutationObserver(() => {
            document.dispatchEvent(new CustomEvent("FinishTranslate"));
            observer.disconnect();
        });

        observer.observe(div, {
            childList: false,
            subtree: true,
            characterDataOldValue: true
        });
    }
}