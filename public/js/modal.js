(() => {
    let modal = {
        /** @type {{ [key: string]: () => void | null }} */
        events: {
            onHide: null,
        },
        reset() {
            modal.removeClass("is-active");
            modal.find("[data-field]").each(function () {
                $(this).html("");
            });
            this.events.onHide = null;
        },

        /**
         * @param {{
         *     title: string;
         *     content: string;
         *     footer: string;
         *     onHide?: () => void;
         * }} params 
         */
        show(params) {
            this.reset();

            const modal = $(this.element);

            for (const [field, value] of Object.entries(params)) {
                const fieldElement = modal.find(`[data-field="${field}"]`);
                if (!fieldElement[0]) continue;
                fieldElement.html(value);
            }

            if (params.onHide) {
                this.events.onHide = onHide;
            }

            modal.addClass("is-active");
            modal.find(".modal-card").addClass("animate__animated animate__zoomIn");
        },
        hide() {
            const modal = $(this.element);

            modal.find(".modal-card").removeClass("animate__animated animate__zoomIn");
            modal.find(".modal-card").addClass("animate__animated animate__zoomOut");

            setTimeout(() => {
                if (this.events.onHide) {
                    this.events.onHide();
                }
                this.reset();
            }, 800);
        }
    };

    modal = new Proxy(modal, {
        get(target, prop) {
            if (prop === "element") return $("#modal")[0];
            return target[prop];
        },
        set(target, prop, value) {
            if (prop === "element") {
                throw new Error("element is readonly");
            }
            target[prop] = value;
            return true;
        }
    });

    window.modal = modal;
})();
