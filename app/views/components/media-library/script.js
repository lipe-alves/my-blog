/**
 * @param {"directory" | "file"} type 
 * @param {string} path 
 */
async function handleOpenItem(type, path) {
    const { toast } = window;

    try {
        if (type === "directory") {
            await handleOpenDirectory(path);
        } else if (type === "file") {
            await handleOpenFile(path);
        }
    } catch (err) {
        toast.error(err.message);
    }
}

async function handleOpenDirectory(directory) {
    const { mediaLibrary } = window.admin;
    await mediaLibrary.setBasePath(directory);
}

async function handleOpenFile(path) {
    
}

async function handleUpdateDirectoryName(span) {
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
    }
}
