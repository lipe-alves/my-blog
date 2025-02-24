(() => {
    if (!window.admin) {
        window.admin = {};
    }

    let mediaLibrary = {
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
        },

        /** @param {string} path */
        async setBasePath(path) {
            this.configs.base_path = path;
            await this.reload();
        },

        async reload() {
            const { api } = window;

            const element = $(this.element);
            const parent = element.parent();

            await api.views.render("media-library", parent[0], this.configs);
        }
    };

    mediaLibrary = new Proxy(mediaLibrary, {
        get(target, prop) {
            if (prop === "element") return $("#media-library")[0];
            return target[prop];
        },
        set(target, prop, value) {
            if (prop === "element") {
                throw new Error("element is readonly");
            }
            target[prop] = value;
            return true;
        }
    });

    window.admin.mediaLibrary = mediaLibrary;
})();
