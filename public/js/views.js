$(document).ready(function () {
    $("[data-view]").each(function () {
        createView(this);
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
        createView(viewElement);
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

    function createView(viewElement) {
        const { convertToCamelCase } = window.functions;

        viewElement = $(viewElement);
        const id = viewElement.attr("id");
        const key = convertToCamelCase(id);
        const loaderId = `loader-${id}`;

        let view = {
            async reload(params = {}, parent = null) {
                const { api } = window;
                const loader = $(`#${loaderId}`).clone(true);

                this.loader.show();

                await api.views.reload(this.element, params, parent);

                $(this.element).append(loader.prop("outerHTML"));

                this.loader.hide();
            },
            loader: {
                show(params = {}) {
                    const { loader } = window;
                    return loader.show(view.element, { ...params, id: loaderId });
                },
                hide() {
                    const { loader } = window;
                    return loader.hide(loaderId);
                }
            }
        };

        view = new Proxy(view, {
            get(target, prop) {
                if (prop === "element") {
                    return $(`#${id}`)[0];
                }
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

        if (!window.views) {
            window.views = {};
        }

        window.views[key] = view;
    }
});
