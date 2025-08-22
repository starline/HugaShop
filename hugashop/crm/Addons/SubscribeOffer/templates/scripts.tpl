<script type="module">
    import { asignFancyAjax } from "{'js/common.js'|asset}";

    let offer_link = "{'AddonSubscribeOfferForm'|link}";
    let show_timer = {$SubscribeOffer->timer|default:0} * 1000;
    let storage_key = 'subscribe-offer';
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
        } else {
            openOffer();
        }
    });
</script>