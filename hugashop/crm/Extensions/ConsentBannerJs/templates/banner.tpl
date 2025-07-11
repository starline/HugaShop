<!-- ConsentBanner -->
<link rel="stylesheet" href="{'ConsentBannerJs/light.css'|asset:'extensions'}" />
<script defer src="{'ConsentBannerJs/cb.min.js'|asset:'extensions'}"></script>
<script>
    window.addEventListener('consent-banner.ready', () => {
        cookiesBannerJs(
            function() {
                try {
                    return JSON.parse(localStorage.getItem('consent_preferences'));
                } catch (error) {
                    return null;
                }
            },
            function(consentState) {
                gtag('consent', 'update', consentState);
                localStorage.setItem('consent_preferences', JSON.stringify(consentState));
            },
            config
        );
    });

    var config = {
        display: {
            mode: "bar"
        },
        consent_types: [{
            name: 'analytics_storage',
            title: "{'Analytics storage'|trans}",
            description: "{'Enables storage, such as cookies, related to analytics (for example, visit duration)'|trans}",
            default: "denied"
        }, {
            name: "ad_storage",
            title: "{'Ads storage'|trans}",
            description: "{'Enables storage, such as cookies, related to advertising'|trans} ",
            default: "denied"
        }, {
            name: 'ad_user_data',
            title: "{'User Data'|trans}",
            description: "{'Sets consent for sending user data to Google for online advertising purposes'|trans}.",
            default: "denied"
        }, {
            name: 'ad_personalization',
            title: "{'Personalization'|trans}",
            description: "{'Sets consent for personalized advertising'|trans}.",
            default: "denied"
        }],
        settings: {
            title: "{'Cookies Settings'|trans}",
            description: "{'In order to provide you with best experience we use various'|trans}...",
            buttons: {
                accept: "{'Accept all'|trans}",
                save: "{'Save preferences'|trans}",
                close: "{'Close'|trans}"
            }
        },
        modal: {
            title: 'Cookies',
            description: "{'We are using various cookies files. Learn more about'|trans} [COOKIE]({$ConsentBannerJs->privacy_link}) {'and make your choice'|trans}.",
            buttons: {
                accept: "{'Accept'|trans}",
                settings: "{'Settings'|trans}"
            }
        }
    };
</script>