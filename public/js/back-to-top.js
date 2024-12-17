$(document).ready(function () {
    const backToTopBtn = $("#back-to-top");

    $(window).on("scroll", scrollFunction);
    backToTopBtn.on("click", backToTop);

    function scrollFunction() {
        if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
        ) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    }

    function backToTop() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
});