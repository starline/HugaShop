<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let offer_link = "{'ExtHelpOfferForm'|link}";
    let show_timer = {$HelpOffer->timer|default:0} * 1000;
    let show_on_leave = {$HelpOffer->show_on_leave|default:0};
    let storage_key = 'help-offer';
    let offer_shown = false;

    function openOffer() {
        if (offer_shown) {
            return;
        }
        offer_shown = true;
        $.fancybox.open({
            type: 'ajax',
            src: offer_link,
            ajax: {
                settings: {
                    method: 'POST',
                    data: {
                        page: window.location.href,
                        csrf: window.csrf
                    }
                }
            },
            touch: false,
            closeExisting: true,
            afterShow: function() {
                localStorage.setItem(storage_key, new Date().toISOString());
                asignFancyAjax();
            }
        });
    }

    $(function() {
        if (localStorage.getItem(storage_key)) {
            return;
        }
        if (show_timer > 0) {
            setTimeout(openOffer, show_timer);
        }
        if (show_on_leave) {
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    openOffer();
                }
            }, { once: true });
        }
    });
</script>