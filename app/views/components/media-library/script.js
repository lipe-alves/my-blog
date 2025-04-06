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
        console.log("err", err);
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
