(() => {
    class Modal {
        /** @type {string} */
        #id;
        /** @type {{ [key: string]: () => void | null }} */
        #events = {
            onHide: null,
        }

        /** @param {string} */
        constructor(id) {
            this.#id = id;
            this.reset();
        }

        get element() {
            return $(`#${this.#id}`)[0];
        }

        reset() {
            const modal = $(this.element);
            modal.removeClass("is-active");
            modal.find("[data-field]").each(function () {
                $(this).html("");
            });
            modal.find(".modal-card").removeClass("animate__animated animate__zoomIn");
            modal.find(".modal-card").removeClass("animate__animated animate__zoomOut");


            for (const event in this.events) {
                this.#events[event] = null;
            }
        }

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

            if (params.view) {
                return new Promise(async (resolve, reject) => {
                    try {
                        const html = await getViewHtml(params.view, params.params);

                        delete params.view;
                        delete params.params;
                        params.content = html;

                        resolve(this.show(params));
                    } catch (err) {
                        reject(err);
                    }
                });
            }

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
                this.#events.onHide = params.onHide;
            }

            this.showAnimateModal();
        }

        hide() {
            const modal = $(this.element);

            modal.find(".modal-card").removeClass("animate__animated animate__zoomIn");
            modal.find(".modal-card").addClass("animate__animated animate__zoomOut");

            setTimeout(() => {
                if (this.#events.onHide) {
                    this.#events.onHide();
                }
                this.reset();
            }, 800);
        }

        showAnimateModal() {
            const modal = $(this.element);
            modal.addClass("is-active");
            modal.find(".modal-card").addClass("animate__animated animate__zoomIn");
        }

        /** @param {string} id */
        static create(id) {
            return new Modal(id);
        }
    }

    const modal = Modal.create("modal");
    window.modal = modal;
    window.functions.createModal = Modal.create;

    async function getViewHtml(viewName, viewParams) {
        const { api } = window;

        const pseudo = document.createElement("div");
        const html = await api.views.render(viewName, pseudo, viewParams);

        return html;
    }
})();
