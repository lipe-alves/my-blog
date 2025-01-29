$(document).ready(function () {
    const { api } = window;

    if (!window.admin) {
        window.admin = {
            settings: {},
            categories: {}
        };
    }

    $("[data-settings]").each(function () {
        const settingsElement = $(this);
        const settings = settingsElement.attr("data-settings");

        settingsElement.attr("contenteditable", "true");

        window.admin.settings[settings] = createController(settingsElement[0], {
            async save() {
                const newSettings = await api.settings.update({ [settings]: this.value });
                return newSettings;
            }
        });
    });

    $("[data-category-id]").each(function () {
        const categoryElement = $(this);
        const categoryId = categoryElement.attr("data-category-id");
        const categoryName = categoryElement.attr("data-category-name");

        categoryElement.attr("contenteditable", "true");

        window.admin.categories[categoryId] = createController(categoryElement[0], {
            async save() {
                alert("save");
            },

            async delete() {
                const { api } = window;
            }
        });
    });

    /** 
     * @param {HTMLElement} controllerElement
     * @param {any} props 
     */
    function createController(controllerElement, props) {
        controllerElement = $(controllerElement);

        let controller = {...props};

        controller = new Proxy(controller, {
            get(target, prop) {
                if (prop === "value") {
                    return controllerElement.text().replace("\n", " ").replace(/\s+/g, " ").trim();
                } else if (prop === "element") {
                    return controllerElement[0];
                }

                return target[prop];
            },
            set(target, prop, value) {
                if (prop === "value") {
                    value = value.replace("\n", " ").replace(/\s+/g, " ").trim();
                    controllerElement.text(value);
                } else if (prop === "element") {
                    return false;
                } else {
                    target[prop] = value;
                }

                return true;
            }
        });

        return controller;
    }
});

async function handleDeleteCategory(button, categoryId) {

}
