$(document).ready(() => {
    const { onEnterPress } = window.myBlog.functions;
    const searchInput = $("#search-input");

    $("html").addClass("has-navbar-fixed-top");

    onEnterPress(searchInput[0], () => {
        alert("Enter " + searchInput.val());
    });
});
