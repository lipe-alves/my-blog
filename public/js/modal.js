(() => {
    let modal = {
        /** @type {{ [key: string]: () => void | null }} */
        events: {
            onHide: null,
        },

        reset() {
            const modal = $(this.element);
            modal.removeClass("is-active");
            modal.find("[data-field]").each(function () {
                $(this).html("");
            });
            modal.find(".modal-card").removeClass("animate__animated animate__zoomIn");
            modal.find(".modal-card").removeClass("animate__animated animate__zoomOut");
            this.events.onHide = null;
        },
        /**
         * @param {{
         *     title: string;
         *     content?: string;
         *     view?: string;
         *     params?: any;
         *     footer: string;
         *     buttons?: string[];
         *     onHide?: () => void;
         * }} params 
         */
        show(params) {
            this.reset();

            const modal = $(this.element);

            if (params.buttons) {
                params.footer = `
                    <div class="buttons">
                        ${params.buttons.join("\n")}
                    </div>
                `;
            }

            for (const [field, value] of Object.entries(params)) {
                const fieldElement = modal.find(`[data-field="${field}"]`);
                if (!fieldElement[0]) continue;
                fieldElement.html(value);
            }

            if (params.onHide) {
                this.events.onHide = params.onHide;
            }

            const showAnimateModal = () => {
                modal.addClass("is-active");
                modal.find(".modal-card").addClass("animate__animated animate__zoomIn");
            };

            if (params.view) {
                return new Promise(async (resolve, reject) => {
                    try {
                        const { api } = window;
                        const { getQueryParams } = window.functions;

                        const viewParams = params.params || getQueryParams();
                        const contentField = $('[data-field="content"]')[0];
                        await api.views.render(params.view, contentField, viewParams);

                        showAnimateModal();

                        resolve();
                    } catch (err) {
                        reject(err);
                    }
                });
            }

            showAnimateModal();
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
