$(document).ready(function () {
    $("[data-view]").each(function () {
        const { convertToCamelCase } = window.functions;

        const viewElement = $(this);
        const id = viewElement.attr("id");
        const key = convertToCamelCase(id);
        const loaderId = `loader-${id}`;

        let view = {
            async reload(params = {}, parent = null) {
                const { api } = window;
                const loader = $(`#${loaderId}`).clone(true);

                this.loader.show();

                await api.reload(this.element, params, parent);

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
            get: function (target, prop) {
                if (prop === "element") {
                    return $(`#${id}`)[0];
                }
                return target[prop];
            },
            set: function (target, prop, value) {
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
    });
});