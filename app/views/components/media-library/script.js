/**
 * @param {"directory" | "file"} type 
 * @param {string} path 
 */
async function handleOpenItem(type, path) {
    if (type === "directory") {
        await handleOpenDirectory(path);
    } else if (type === "file") {
        await handleOpenFile(path);
    }
}

async function handleOpenDirectory(directory) {
    const { mediaLibrary } = window.admin;
    await mediaLibrary.setBasePath(directory);
}

async function handleOpenFile(path) {
    
}