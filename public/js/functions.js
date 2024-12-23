(() => {
    /**
     * @param {HTMLElement} htmlElement
     * @param {(evt: MouseEvent) => void} callback
     * @returns {void}
     */
    function onEnterPress(evt, callback) {
        if (evt.key === "Enter") {
            evt.preventDefault();
            callback(evt);
        }
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

    /** @param {{ [key: string]: any }} params */
    function setQueryParams(params) {
        const url = new URL(window.location.href);
        
        for (const key of url.searchParams.keys()) {
            url.searchParams.delete(key);
        }

        for (const [key, value] of Object.entries(params)) {
            url.searchParams.set(key, value);
        }

        window.history.pushState(null, '', url.toString());
    }

    /** @returns {{ [key: string]: any }} */
    function getQueryParams() {
        const url = new URL(window.location.href);
        const params = {};

        for (const [key, value] of url.searchParams.entries()) {
            params[key] = value;
        }

        return params;
    }

    /** 
     * @param {{ [key: string]: any }} 
     * @returns {string}
     */
    function createQueryString(params) {
        const url = new URL(window.location.href);

        for (const [key, value] of Object.entries(params)) {
            url.searchParams.set(key, value);
        }

        return url.search;
    }

    window.myBlog.functions = {
        onEnterPress,
        createEndpoint,
        setQueryParams,
        getQueryParams,
        createQueryString
    };
})();
