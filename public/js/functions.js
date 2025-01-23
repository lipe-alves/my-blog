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
                url = url.replace(/(?<!:)\/+/g, "/");

                const resp = await fetch(url, config);
                if (!resp.ok) throw await resp.json();

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
            if (!value) continue;
            url.searchParams.set(key, value);
        }

        window.history.pushState(null, "", url.toString());
    }

    function clearQueryParams() {
        const query = getQueryParams();

        for (const key in query) {
            query[key] = "";
        }

        setQueryParams(query);
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

    /** @param {string} str */
    function stringToColor(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }
        let color = "#";
        for (let i = 0; i < 3; i++) {
            const value = (hash >> (i * 8)) & 0xFF;
            color += ("00" + value.toString(16)).substr(-2);
        }
        return color;
    }

    /** @param {string} str */
    function removeWhitespaces(str) {
        return str.replace(/\s+/g, " ").trim();
    }

    function removeNewlines(str) {
        return str.replace(/\n/g, " ").trim();
    }

    function generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    /** @param {string} str */
    function convertToCamelCase(str) {
        return str
            .replace(/_/g, "-")
            .replace(/\b(\w)/g, (char) => char.toUpperCase())
            .replace(/\s+/g, "")
            .replace(/^(\w)/, (char) => char.toLowerCase())
            .replace(/[^\w]/g, "");
    }

    /**
     * @param {() => Promise<any>} callback 
     * @param {number} delay 
     * @returns {Promise<any>} 
     */
    function delayAsync(callback, delay) {
        return new Promise((resolve, reject) => {
            setTimeout(async () => {
                try {
                    const result = await callback();
                    resolve(result);
                } catch (err) {
                    reject(err);
                }
            }, delay);
        });
    }

    window.functions = {
        onEnterPress,
        createEndpoint,
        setQueryParams,
        getQueryParams,
        clearQueryParams,
        createQueryString,
        stringToColor,
        removeWhitespaces,
        removeNewlines,
        generateId,
        convertToCamelCase,
        delayAsync
    };
})();
