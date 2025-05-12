/** @param {string} categoryId */
function handleRemoveCategory(categoryId) {
    window.admin.post.categories.delete(categoryId);
}

function handleAddCategory() {
    const { id, name } = window.admin.post.categories.selected;
    window.admin.post.categories.add(id, name);
}

async function handlePublishPost(button) {
    const { admin, toast } = window;
    const { delayAsync, setButtonLoading } = window.functions;

    try {
        setButtonLoading(button, true);
        await delayAsync(() => admin.post.publishPost(), 3000);
        toast.success("Post publicado com sucesso!");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setButtonLoading(button, false);
    }
}

async function handleChangeProfilePhoto() {
    const { admin } = window;

    await admin.mediaLibrary.show({
        accept: "image/*",
        multiple: false
    });

    admin.mediaLibrary.addEventListener("send-files", (files) => {
        admin.mediaLibrary.hide();
        
        const file = files[0];
        if (!file) return;

        admin.settings.writer_photo.value = file.src;
    });
}
