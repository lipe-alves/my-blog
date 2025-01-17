(() => {
    const { apiUrl } = window;
    const { createEndpoint, createQueryString } = window.functions;
    const apiEndpoint = createEndpoint(apiUrl);

    const api = {
        apiEndpoint,

        /** @param {RequestInit=} config */
        async ping(config = {}) {
            const resp = await this.apiEndpoint.request("/ping", {
                ...config,
                method: "GET"
            });
            return resp.json();
        },

        /** 
         * @param {HTMLElment} viewElement 
         * @param {{ [key: string]: any }} params
         * @param {HTMLElement=} parent
         * @returns {string}
         */
        async reload(viewElement, params = {}, parent = null) {
            viewElement = $(viewElement);

            const view = viewElement.attr("data-view");
            params.view = view;

            const baseUrl = window.location.href.replace(window.location.search, "");
            const viewEndpoint = createEndpoint(baseUrl);
            const queryString = createQueryString(params);

            const resp = await viewEndpoint.get(`/${queryString}`);
            const html = await resp.text();

            parent = parent ? $(parent) : viewElement;
            parent.prop("outerHTML", html);

            return html;
        },

        posts: {
            async search(params) {
                const queryString = createQueryString(params);
                const resp = await apiEndpoint.get(`/posts/${queryString}`);
                return resp.json();
            },
        },

        comments: {
            async search(params) {
                const queryString = createQueryString(params);
                const resp = await apiEndpoint.get(`/comments/${queryString}`);
                return resp.json();
            },

            /**
             * @param {{
             *     post_id: string;
             *     comment: string;
             *     reply_to?: string;
             *     fullname?: string;
             *     photo?: string;
             *     email: string;
             * }} params 
             */
            async send(params) {
                const resp = await apiEndpoint.post("/comments/", params);
                return resp.json();
            }
        }
    };

    window.api = api;
})();
