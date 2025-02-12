(() => {
    if (!window.admin) {
        window.admin = {};
    }

    const mediaLibrary = {
        async show() {
            const { modal } = window;

            await modal.show({
                title: "Biblioteca de Mídia",
                view: "media-library"
            });


        }
    };

    window.admin.mediaLibrary = mediaLibrary;
})();