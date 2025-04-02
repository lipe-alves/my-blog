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

async function handleUpdateDirectoryName(span, oldName) {
    const { toast } = window;
    const { removeWhitespaces, removeNewlines } = window.functions;
    const { mediaLibrary } = window.admin;
    
    span = $(span);
    
    let name = span.text();
    name = removeNewlines(name);
    name = removeWhitespaces(name);
    
    span.text(name);

    try {
        await mediaLibrary.rename(mediaLibrary.currentPath, name);
    } catch (err) {
        toast.error(err.message);
        span.text(oldName);
    }
}
