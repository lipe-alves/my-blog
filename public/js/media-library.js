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

        get currentPath() {
            const breadcrumb = $(this.element).find(".breadcrumb");
            return breadcrumb.attr("data-current-path");
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

        /**
         * @param {string} path 
         * @param {string} newName 
         */
        async rename(path, newName) {
            const { api } = window;
            const data = await api.media.rename(path, newName);

            let oldName = path.split("/");
            oldName = oldName[oldName.length - 1];

            const newCurrentPath = path.replace(oldName, newName);
            await this.setBasePath(newCurrentPath);

            return data;
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
