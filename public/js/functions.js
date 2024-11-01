(() => {
    /**
     * Adds a listener event to the Enter press
     * @param {HTMLElement} htmlElement
     * @param {(evt: MouseEvent) => void} callback
     * @returns {void}
     */
    function onEnterPress(htmlElement, callback) {
        $(htmlElement).on("keypress", function (evt) {
            if (evt.key === "Enter") {
                evt.preventDefault();
                callback.bind(this, evt);
            }
        });
    }

    /** @param {string} baseUrl */
    function createEndpoint(baseUrl) {
        return {
            /**
             * @param {string} path
             * @param {RequestInit} config
             */
            async request(path, config) {
                let url = baseUrl;
                url = url.replace(/\/$/, "");
                path = path.replace(/^\//, "");
                path = "/" + path;
                url += path;

                const resp = await fetch(url, config);
                return resp;
            },

            /**
             * @param {string} path
             * @param {RequestInit=} config
             */
            get(path, config = {}) {
                return this.request(path, {
                    ...config,
                    method: "GET"
                });
            },

            /**
             * @param {string} path
             * @param {{
             *     [key: string]: any;
             * }} data
             * @param {RequestInit=} config
             */
            post(path, data, config = {}) {
                return this.request(path, {
                    ...config,
                    method: "POST",
                    body: JSON.stringify(data),
                });
            },

            /**
             * @param {string} path
             * @param {{
             *     [key: string]: any;
             * }} data
             * @param {RequestInit=} config
             */
            patch(path, data, config = {}) {
                return this.request(path, {
                    ...config,
                    method: "PATCH",
                    body: JSON.stringify(data),
                });
            },

            /**
             * @param {string} path
             * @param {{
             *     [key: string]: any;
             * }} data
             * @param {RequestInit=} config
             */
            put(path, data, config = {}) {
                return this.request(path, {
                    ...config,
                    method: "PUT",
                    body: JSON.stringify(data),
                });
            },

            /**
             * @param {string} path
            * @param {RequestInit=} config
            */
            delete(path, config = {}) {
                return this.request(path, {
                    ...config,
                    method: "DELETE",
                });
            },
        };
    }

    window.myBlog.functions = {
        onEnterPress,
        createEndpoint,
    };
})();
