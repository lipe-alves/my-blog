/** @param {string} path */
async function handleOpenItem(path) {
    const { toast } = window;
    try {
        const { mediaLibrary } = window.admin;
        await mediaLibrary.setBasePath(path);
    } catch (err) {
        toast.error(err.message);
    }
}

/**
 * @param {Event} event 
 * @param {string} path 
 * @param {string} oldName 
 */
async function handleRenameMediaItem(event, path, oldName) {
    event.stopPropagation();

    const { toast } = window;
    const { removeWhitespaces, removeNewlines } = window.functions;
    const { mediaLibrary } = window.admin;

    const span = $(event.target);

    let newName = span.text();
    newName = removeNewlines(newName);
    newName = removeWhitespaces(newName);

    span.text(newName);

    try {
        await mediaLibrary.rename(path, newName);
    } catch (err) {
        toast.error(err.message);
        span.text(oldName);
    }
}

/** @param {string} url */
function handleOpenFile(url) {
    const a = document.createElement("a");
    a.href = url;
    a.target = "_BLANK";
    a.click();
}

/**
 * @param {Event} event 
 * @param {string} path 
 */
function handleOpenMediaItemDeletionModal(event, path) {
    event.stopPropagation();

    const { mediaLibrary } = window.admin;

    mediaLibrary.modal.show({
        title: `Tem certeza que deseja excluir "${path}"?`,
        buttons: [
            `<button
                class="button" 
                onclick="window.admin.mediaLibrary.modal.hide()"
            >
                Cancelar
            </button>`,
            `<button 
                class="button is-danger" 
                onclick="handleDeleteMediaItem('${path}')"
            >
                Excluir
            </button>`
        ],
    });
}

async function handleDeleteMediaItem(path) {
    const { toast } = window;
    const { mediaLibrary } = window.admin;

    try {
        await mediaLibrary.delete(path);
        toast.success("Item excluído com sucesso!");
    } catch (err) {
        toast.error(err.message);
    }
}

async function handleUploadFile() {
    const { toast } = window;
    const { mediaLibrary } = window.admin;

    const uploadFile = async evt => {
        const files = Array.from(evt.target.files);
        if (files.length === 0) return;

        try {
            await mediaLibrary.upload(mediaLibrary.currentPath, files);
            toast.success("Arquivo enviado com sucesso!");
        } catch (err) {
            toast.error(err.message);
        }
    };

    const input = document.createElement("input");
    input.type = "file";
    input.multiple = true;
    input.onchange = uploadFile;
    input.click();
}

function handleOpenUploadFolderModal() {
    const { mediaLibrary } = window.admin;

    mediaLibrary.modal.show({
        title: "Nova pasta",
        content: `
            <div class="field">
                <label class="label">
                    Digite o nome da pasta:
                </label>
                <div class="control">
                    <input
                        id="new-folder-name"
                        class="input"
                        type="text"
                        placeholder="Nome da pasta"
                    >
                </div>
            </div>
        `,
        buttons: [
            `<button
                class="button is-danger" 
                onclick="window.admin.mediaLibrary.modal.hide()"
            >
                Cancelar
            </button>`,
            `<button 
                class="button is-success" 
                onclick="handleUploadFolder(this)"
            >
                Enviar
            </button>`
        ],
    });
}

/** @param {HTMLButtonElement} button */
async function handleUploadFolder(button) {
    const { mediaLibrary } = window.admin;
    const { removeWhitespaces, removeNewlines, setButtonLoading } = window.functions;

    const input = $("#new-folder-name");
    let name = input.val();
    name = removeWhitespaces(name);
    name = removeNewlines(name);

    try {
        setButtonLoading(button, true);
        await mediaLibrary.createFolder(mediaLibrary.currentPath, name);
        toast.success("Pasta adicionada com sucesso!");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setButtonLoading(button, false);
    }
}

function handleSelectFile(checkbox) {
    const { mediaLibrary } = window.admin;

    let someChecked = false;
    const checked = checkbox.checked;
    if (checked)
        someChecked = true;

    const sendButton = $("#send-selected-items-button")[0];
    const multiple = Boolean(mediaLibrary.configs.multiple);

    $(".MediaLibrary-item .checkbox").each(function () {
        const box = this;

        if (checked && !multiple)
            box.checked = false;

        if (box.checked)
            someChecked = true;
    });

    checkbox.checked = checked;

    if (someChecked) {
        sendButton.disabled = false;
        sendButton.innerHTML = "Enviar arquivo";
        if (multiple)
            sendButton.innerHTML += "s";
    } else {
        sendButton.disabled = true;
        sendButton.innerHTML = "Selecione ";
        sendButton.innerHTML += multiple ? " arquivos" : "um arquivo";
    }
}

function handleSendFiles() {
    const { mediaLibrary } = window.admin;

    const container = $(mediaLibrary.element);
    const files = [];

    container.find(".MediaLibrary-item").each(function () {
        const item = $(this);
        const checkbox = item.find('input[type="checkbox"]')[0];
        if (checkbox?.checked) {
            const file = { ...this.dataset };
            files.push(file);
        }
    });

    mediaLibrary.dispatchEvent("send-files", files);
}
