(() => {
    class MediaLibrary {
        #configs;
        #id;

        constructor(mediaLibraryId) {
            this.#id = mediaLibraryId;
            this.#configs = {
                media_type: "image/*"
            };
        }

        get element() {
            return $(`#${this.#id}`)[0];
        }

        get configs() {
            return this.#configs;
        }

        async show(configs = {}) {
            const { modal } = window;

            this.#configs = configs;

            await modal.show({
                title: "Biblioteca de Mídia",
                view: "media-library",
                params: configs
            });
        }

        /** @param {string} path */
        async setBasePath(path) {
            this.#configs.base_path = path;
            await this.reload();
        }

        async reload() {
            const { api } = window;

            const element = $(this.element);
            const parent = element.parent();

            await api.views.render("media-library", parent[0], this.#configs);
        }
    }
    
    if (!window.admin) {
        window.admin = {};
    }

    window.admin.mediaLibrary = new MediaLibrary("media-library");
})();
