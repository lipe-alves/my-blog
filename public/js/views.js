class View {
    #id;
    #loaderId;

    /** @param {HTMLElement} viewElement */
    constructor(viewElement) {
        viewElement = $(viewElement);
        this.#id = viewElement.attr("id");
        this.#loaderId = `loader-${this.#id}`;
    }

    get id() {
        return this.#id;
    }

    /** @returns {HTMLElement} */
    get element() {
        return $(`#${this.#id}`)[0];
    }

    get loader() {
        const view = this;
        const loaderId = this.#loaderId;

        return {
            show(params = {}) {
                const { loader } = window;
                return loader.show(view.element, { ...params, id: loaderId });
            },
            hide() {
                const { loader } = window;
                return loader.hide(loaderId);
            }
        }
    }

    async reload(params = {}, parent = null) {
        const { api } = window;
        const loader = $(`#${this.#loaderId}`).clone(true);

        this.loader.show();

        await api.views.reload(this.element, params, parent);

        $(this.element).append(loader.prop("outerHTML"));

        this.loader.hide();
    }

    static create(viewElement) {
        const { convertToCamelCase } = window.functions;

        const view = new View(viewElement);

        const key = convertToCamelCase(view.id);
        if (!window.views) {
            window.views = {};
        }

        window.views[key] = view;

        return view;
    }
}

$(document).ready(function () {
    $("[data-view]").each(function () {
        View.create(this);
    });

    window.views.renderNewView = async function (viewName, viewParent, params) {
        const { api } = window;
        const { getQueryParams } = window.functions;

        if (!params) {
            const query = getQueryParams();
            params = query;
        }
        
        await api.views.render(viewName, viewParent, params);

        const viewElement = $(viewParent).find(`[data-view="${viewName}"]`);
        View.create(viewElement);
    };

    window.views.reloadAllViews = async function () {
        const { getQueryParams } = window.functions;
        const query = getQueryParams();

        for (const view of Object.values(window.views)) {
            if ("reload" in view) {
                await view.reload(query);
            }
        }
    };
});
