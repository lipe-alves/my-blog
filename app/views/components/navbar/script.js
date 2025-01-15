function handleToggleBurger(evt, burgerIcon) {
    evt.preventDefault();

    burgerIcon = $(burgerIcon);
    const targetId = burgerIcon.attr("data-target");
    const dropMenu = $(`#${targetId}`);

    burgerIcon.toggleClass("is-active");
    dropMenu.toggleClass("is-active");

    const ariaExpanded = burgerIcon.attr("aria-expanded") === "true";
    burgerIcon.attr("aria-expanded", String(!ariaExpanded));
}

$(document).ready(() => {
    $("html").addClass("has-navbar-fixed-top");
});
