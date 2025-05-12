(() => {
    const { createTextEditor } = window.functions;
    createTextEditor('[data-settings="writer_about"]');
})();

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
