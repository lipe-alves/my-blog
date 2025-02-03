$(document).ready(function () {
    if (!window.admin) {
        window.admin = {
            settings: {},
            categories: {}
        };
    }

    $("[data-settings]").each(function () {
        const settingsElement = $(this);
        const settings = settingsElement.attr("data-settings");
        window.admin.settings[settings] = createController(settingsElement[0]);
    });

    $("[data-category-id]").each(function () {
        const categoryElement = $(this);
        const categoryId = categoryElement.attr("data-category-id");
        window.admin.categories[categoryId] = createController(categoryElement[0]);
    });
});

async function reloadAllViews() {
    const { getQueryParams } = window.functions;
    const query = getQueryParams();

    for (const view of Object.values(window.views)) {
        await view.reload(query);
    }
}

/** 
 * @param {HTMLElement} controllerElement
 * @param {any} props 
 */
function createController(controllerElement, props = {}) {
    controllerElement = $(controllerElement);

    let controller = { ...props, old: "" };

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

    controller.old = controller.value;

    return controller;
}

async function handleDeleteCategory(button, categoryId) {
    const { modal, toast } = window;
    const { delayAsync } = window.functions;

    const successButton = $(button);
    const cancellationButton = $(`#cancel-${categoryId}-deletion`);
    const postsNewCategoryId = $(`#posts-new-category`).val();

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        successButton.prop("disabled", disabled);
        cancellationButton.prop("disabled", disabled);
        successButton.toggleClass("is-loading");
    };

    const deleteCategory = async () => {
        const { api } = window;

        const resp = await api.categories.delete(categoryId, postsNewCategoryId);
        window.admin.categories[categoryId] = null;

        await reloadAllViews();

        return resp;
    };

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

    const categories = [];

    $("[data-category-id]").each(function () {
        const id = $(this).attr("data-category-id");
        const name = $(this).attr("data-category-name");
        if (id == categoryId) return;

        categories.push({ id, name });
    });

    const options = categories.map(category => `
        <option value="${category.id}">
            ${category.name}
        </option>
    `).join("");

    modal.show({
        title: `Tem certeza que deseja excluir "${categoryName}"?`,
        content: `
            <div class="field">
                <label class="label">
                    Mover os posts desta categoria para:
                </label>
                <div class="control">
                    <div class="select">
                        <select id="posts-new-category">
                            ${options}
                        </select>
                    </div>
                </div>
            </div>
        `,
        buttons: [
            `<button
                id="cancel-${categoryId}-deletion"
                class="button" 
                onclick="window.modal.hide()"
            >
                Cancelar
            </button>`,
            `<button 
                class="button is-danger" 
                onclick="handleDeleteCategory(this, ${categoryId})"
            >
                Excluir
            </button>`
        ],
    });

}

async function handleAddNewCategory(button) {
    const { api, toast } = window;
    const { delayAsync, removeWhitespaces, removeNewlines } = window.functions;

    button = $(button);
    const nameFieldElement = $('[data-new-category="true"]');
    let newCategoryName = nameFieldElement.text();
    newCategoryName = removeWhitespaces(newCategoryName);
    newCategoryName = removeNewlines(newCategoryName);

    /** @param {boolean} disabled */
    const setButtonDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button.toggleClass("is-loading");
    };

    const createCategory = async () => {
        const category = await api.categories.create({ name: newCategoryName });
        const categoryElement = $(`[data-category-id="${category.id}"]`)[0];
        window.admin.categories[category.id] = createController(categoryElement);

        await reloadAllViews();

        return category;
    };

    try {
        setButtonDisabled(true);
        await delayAsync(createCategory, 3000);
        toast.success("Categoria adicionada com sucesso!");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setButtonDisabled(false);
    }
}

async function handleSaveChanges(button) {
    const { api, admin, toast } = window;
    const { delayAsync } = window.functions;

    button = $(button);

    /** @param {boolean} disabled */
    const setButtonDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button.toggleClass("is-loading");
    };

    const saveChanges = async () => {
        const settingsUpdates = {};

        for (const [settings, controller] of Object.entries(admin.settings)) {
            if (controller.value === controller.old) continue;
            settingsUpdates[settings] = controller.value;
        }

        const madeChanges = Object.keys(settingsUpdates).length > 0;
        if (madeChanges) await api.settings.update(settingsUpdates);

        for (const [categoryId, controller] of Object.entries(admin.categories)) {
            if (controller.value === controller.old) continue;
            const name = controller.value;
            await api.categories.update(categoryId, { name });
        }
    };

    try {
        setButtonDisabled(true);

        await delayAsync(saveChanges, 3000);

        toast.success("Alterações salvas com sucesso!");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setButtonDisabled(false);
    }
}

async function handleResetChanges(button) {
    const { delayAsync } = window.functions;

    button = $(button);

    /** @param {boolean} disabled */
    const setButtonDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button.toggleClass("is-loading");
    };

    const resetChanges = () => {
        for (const controller of Object.values(admin.settings)) {
            controller.value = controller.old;
        }

        for (const controller of Object.values(admin.categories)) {
            controller.value = controller.old;
        }
    };

    setButtonDisabled(true);
    await delayAsync(resetChanges, 1500);
    setButtonDisabled(false);
}
