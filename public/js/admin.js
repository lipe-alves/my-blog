$(document).ready(function () {
    if (!window.admin) {
        window.admin = {};
    }

    if (!window.admin.settings) {
        window.admin.settings = {};
    }

    if (!window.admin.categories) {
        window.admin.categories = {};
    }

    $("[data-settings]").each(function () {
        const settingsElement = $(this);
        const settings = settingsElement.attr("data-settings");
        window.admin.settings[settings] = createController(`[data-settings="${settings}"]`);
    });

    $("[data-category-id]").each(function () {
        const categoryElement = $(this);
        const categoryId = categoryElement.attr("data-category-id");
        window.admin.categories[categoryId] = createController(`[data-category-id="${categoryId}"]`);
    });

    window.admin.reset = function () {
        for (const controller of Object.values(admin.settings)) {
            if (!controller) continue;
            controller.value = controller.old;
        }

        for (const controller of Object.values(admin.categories)) {
            if (!controller) continue;
            controller.value = controller.old;
        }
    };

    window.admin.save = async function () {
        const settingsUpdates = {};

        for (const [settings, controller] of Object.entries(admin.settings)) {
            if (!controller) continue;
            if (controller.value === controller.old) continue;
            settingsUpdates[settings] = controller.value;
        }

        let madeChanges = Object.keys(settingsUpdates).length > 0;
        if (madeChanges) await api.settings.update(settingsUpdates);

        for (const [categoryId, controller] of Object.entries(admin.categories)) {
            if (!controller) continue;
            if (controller.value === controller.old) continue;
            const name = controller.value;
            await api.categories.update(categoryId, { name });
            madeChanges = true;
        }

        return madeChanges;
    };
});

/** 
 * @param {string} selector
 * @param {any} props 
 */
function createController(selector, props = {}) {
    let controller = { ...props, old: "" };

    controller = new Proxy(controller, {
        get(target, prop) {
            if (prop === "value") {
                return $(selector).text().replace("\n", " ").replace(/\s+/g, " ").trim();
            } else if (prop === "element") {
                return $(selector);
            }

            return target[prop];
        },
        set(target, prop, value) {
            if (prop === "value") {
                value = value.replace("\n", " ").replace(/\s+/g, " ").trim();
                $(selector).text(value);
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

        await window.views.postFilters.reload();

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
        window.admin.categories[category.id] = createController(`[data-category-id="${category.id}"]`);

        await window.views.postFilters.reload();

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
    const { toast } = window;
    const { delayAsync } = window.functions;

    button = $(button);

    /** @param {boolean} disabled */
    const setButtonDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button.toggleClass("is-loading");
    };

    try {
        setButtonDisabled(true);

        const changesMade = await delayAsync(window.admin.save, 3000);

        if (changesMade) {
            toast.success("Alterações salvas com sucesso!");
        } else {
            toast.warning("Nenhuma alteração foi feita!");
        }
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

    setButtonDisabled(true);
    await delayAsync(window.admin.reset, 1500);
    setButtonDisabled(false);
}

async function handleLogout(button) {
    const { api, toast } = window;
    const { delayAsync } = window.functions;

    button = $(button);

    /** @param {boolean} disabled */
    const setButtonDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button.toggleClass("is-loading");
    };

    const logout = async () => {
        const resp = await api.admin.logout();
        return resp;
    };

    try {
        setButtonDisabled(true);

        const resp = await delayAsync(logout, 3000);
        setButtonDisabled(false);

        toast.success(resp.message);
        await delayAsync(() => window.location.reload(), 2000);
    } catch (err) {
        setButtonDisabled(false);
        toast.error(err.message);
    }
}
