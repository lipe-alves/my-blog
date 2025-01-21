(() => {
    const modal = $("#modal");
    window.modal = {
        element: modal[0],
        /**
         * @param {{
         *     title: string;
         *     content: string;
         *     footer: string;
         * }} params 
         */
        show(params) {
            for (const [field, value] of Object.entries(params)) {
                modal.find(`[data-field="${field}"]`).html(value);
            }

            modal.addClass("is-active");
        },
        hide() {
            modal.removeClass("is-active");

            modal.find("[data-field]").each(function () {
                $(this).html(""); 
            });
        }
    };
})();
