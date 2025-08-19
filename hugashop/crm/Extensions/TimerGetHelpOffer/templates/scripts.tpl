<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let offer_link   = "{'ExtTimerGetHelpOfferForm'|link}";
    let showTimer    = {$TimerGetHelpOffer->timer|default:0} * 1000;
    let showOnLeave  = {$TimerGetHelpOffer->show_on_leave|default:0};
    let offerShown   = false;

    function openOffer() {
        if (offerShown) {
            return;
        }
        offerShown = true;
        $.fancybox.open({
            type: 'ajax',
            src: offer_link,
            ajax: {
                settings: {
                    method: 'POST',
                    data: {
                        csrf: window.csrf
                    }
                }
            },
            touch: false,
            closeExisting: true,
            afterShow: asignFancyAjax
        });
    }

    $(function() {
        if (showTimer > 0) {
            setTimeout(openOffer, showTimer);
        }
        if (showOnLeave) {
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    openOffer();
                }
            }, { once: true });
        }
    });
</script>
