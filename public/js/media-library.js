(() => {
    const { api, modal } = window;
    const { createModal } = window.functions;

    class MediaLibrary {
        #configs;
        #id;
        #modal;
        #events;

        static DEFAULT_CONFIGS = {
            accept: "*",
            multiple: false
        };

        constructor(mediaLibraryId) {
            this.#id = mediaLibraryId;
            this.#configs = { ...MediaLibrary.DEFAULT_CONFIGS };
            this.#events = {};
            this.#modal = createModal("media-library-modal");
        }

        get configs() {
            return this.#configs;
        }

        get modal() {
            return this.#modal;
        }

        get element() {
            return $(`#${this.#id}`)[0];
        }

        get currentPath() {
            const breadcrumb = $(this.element).find(".breadcrumb");
            return breadcrumb.attr("data-current-path");
        }

        async show(configs = {}) {
            this.#configs = configs;
            this.#events = {};

            await modal.show({
                title: "Biblioteca de Mídia",
                view: "media-library",
                params: configs,
                hideFooter: true
            });
        }

        hide() {
            this.#configs = { ...MediaLibrary.DEFAULT_CONFIGS };
            this.#events = {};

            modal.hide();
        }

        /**
         * @param {string} event 
         * @param {(...args: any[]) => void} callback 
         */
        addEventListener(event, callback) {
            this.#events[event] = callback;
        }

        /**
         * @param {string} event 
         * @param {...args: any[]} args 
         */
        dispatchEvent(event, ...args) {
            this.#events[event](...args);
        }

        /** @param {string} path */
        async setBasePath(path) {
            this.#configs.base_path = path;
            await this.reload();
        }

        /**
         * @param {string} path 
         * @param {File[]} files 
         */
        async upload(path, files) {
            const data = await api.media.upload(path, files);
            await this.reload();
            return data;
        }

        /**
         * @param {string} path 
         * @param {string} name 
         */
        async createFolder(path, name) {
            const data = await api.media.createFolder(path, name);
            await this.reload();
            return data;
        }

        /**
         * @param {string} path 
         * @param {string} newName 
         */
        async rename(path, newName) {
            const data = await api.media.rename(path, newName);

            let oldName = path.split("/");
            oldName = oldName[oldName.length - 1];

            const newCurrentPath = path.replace(oldName, newName);
            await this.setBasePath(newCurrentPath);

            return data;
        }

        /** @param {string} path */
        async delete(path) {
            const data = await api.media.delete(path);
            await this.reload();

            return data;
        }

        async reload() {
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
