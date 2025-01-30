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

        categoryElement.attr("contenteditable", "true");

        window.admin.categories[categoryId] = createController(categoryElement[0], {
            async save() {
                alert("save");
            },

            async delete() {
                const { api, views } = window;
                const { getQueryParams } = window.functions;

                const resp = await api.categories.delete(categoryId);

                const query = getQueryParams();
                await views.postFilters.reload(query);

                return resp;
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
    const { modal, toast, admin } = window;
    const { delayAsync } = window.functions;

    const successButton = $(button);
    const cancellationButton = $(`#cancel-${categoryId}-deletion`);

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        successButton.prop("disabled", disabled);
        cancellationButton.prop("disabled", disabled);
        successButton.toggleClass("is-loading");
    };

    const deleteCategory = admin.categories[categoryId].delete;
    
    try {
        setFormDisabled(true);
        const resp = await delayAsync(deleteCategory, 3000);
        toast.success(resp.message);
        modal.hide();
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}

function handleOpenCategoryDeletionModal(categoryId) {
    const { modal } = window;

    const categoryName = $(`[data-category-id="${categoryId}"]`).attr("data-category-name");

    modal.show({
        title: `Tem certeza que deseja excluir "${categoryName}"?`,
        content: `
            <p></p>
        `,
        footer: `
            <div class="buttons">
                <button
                    id="cancel-${categoryId}-deletion"
                    class="button" 
                    onclick="window.modal.hide()"
                >
                    Cancelar
                </button>
                <button 
                    class="button is-danger" 
                    onclick="handleDeleteCategory(this, ${categoryId})"
                >
                    Excluir
                </button>
            </div>
        `,
    });

}
