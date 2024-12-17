$(document).ready(function () {
    const backToTopBtn = $("#btn-back-to-top");

    $(window).on("scroll", onWindowScroll);
    backToTopBtn.on("click", goBackToTop);

    function onWindowScroll() {
        const showButton =
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20;
        
        backToTopBtn.css("display", showButton ? "block" : "none");
    }

    function goBackToTop() {
        $("html, body").animate({ scrollTop: 0 }, 300);
    }
});
