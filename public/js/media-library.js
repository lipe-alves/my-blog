(() => {
    if (!window.admin) {
        window.admin = {};
    }

    const mediaLibrary = {
        configs: {
            media_type: "image/*"
        },

        async show(configs = {}) {
            const { modal } = window;

            this.configs = configs;

            await modal.show({
                title: "Biblioteca de Mídia",
                view: "media-library",
                params: configs
            });

            
        }
    };

    window.admin.mediaLibrary = mediaLibrary;
})();
