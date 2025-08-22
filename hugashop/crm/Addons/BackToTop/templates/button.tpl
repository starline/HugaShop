<!-- BackToTop -->
<link rel="stylesheet" href="{'button.css'|asset:'BackToTop':'addon'}" />
<div class="back-to-top"></div>
<script>
    let mobile_show = parseInt('{$BackToTop->mobile_show}');
    let minClientWidth = 540; // px

    function trackScroll() {
        let scrollY = window.scrollY;
        let clientHeight = document.documentElement.clientHeight;
        let clientWidth = document.documentElement.clientWidth;
        let show = (mobile_show || clientWidth >= minClientWidth) ? true : false

        if (scrollY > clientHeight && show) {
            goTopBtn.classList.add('back-to-top-show');
        }
        if (scrollY < clientHeight) {
            goTopBtn.classList.remove('back-to-top-show');
        }
    }

    function backToTop() {
        if (window.scrollY > 0) {
            document.body.scrollTop = 0; // For Safari
            document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
        }
    }

    let goTopBtn = document.querySelector('.back-to-top');
    if (goTopBtn) {
        window.addEventListener('scroll', trackScroll);
        goTopBtn.addEventListener('click', backToTop);
    }
</script>