/**
 * Type keywords for search field
 * 
 * @author Andri Huga
 * @version 1.1
 * 
 */

(function ($) {
    $.fn.typePlaceholder = function (options) {
        const settings = $.extend({
            words: [],
            delayBetweenWords: 1500,
            typeSpeed: 100,
            eraseSpeed: 40,
            loop: true
        }, options);

        return this.each(function () {
            const $el = $(this);
            let wordIndex = 0;
            let charIndex = 0;
            let typing = true;
            let stoppedByUser = false;
            let timeout;

            function typeLoop() {
                if (stoppedByUser) return;

                const word = settings.words[wordIndex];

                if (typing) {
                    if (charIndex <= word.length) {
                        $el.attr('placeholder', word.substring(0, charIndex));
                        charIndex++;
                        timeout = setTimeout(typeLoop, settings.typeSpeed);
                    } else {
                        typing = false;
                        timeout = setTimeout(typeLoop, settings.delayBetweenWords);
                    }
                } else {
                    if (charIndex >= 0) {
                        $el.attr('placeholder', word.substring(0, charIndex));
                        charIndex--;
                        timeout = setTimeout(typeLoop, settings.eraseSpeed);
                    } else {
                        typing = true;
                        wordIndex = (wordIndex + 1) % settings.words.length;
                        if (!settings.loop && wordIndex === 0) return;
                        timeout = setTimeout(typeLoop, 300);
                    }
                }
            }

            // Остановка только при пользовательском вводе текста
            $el.on('input', function () {
                const val = $el.val();
                if (val.length > 0) {
                    stoppedByUser = true;
                    clearTimeout(timeout);
                } else if (stoppedByUser) {
                    // пользователь очистил поле, запускаем снова
                    stoppedByUser = false;
                    charIndex = 0;
                    typing = true;
                    typeLoop();
                }
            });

            typeLoop();
        });
    };
})(jQuery);