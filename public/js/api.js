(() => {
    const { apiUrl, baseUri } = window;
    const { createEndpoint, createQueryString } = window.functions;
    const apiEndpoint = createEndpoint(apiUrl);
    const viewEndpoint = createEndpoint(baseUri);

    const api = {
        apiEndpoint,
        viewEndpoint,

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
         * @returns {string}
         */
        async reload(viewElement, params = {}) {
            console.log({ viewElement, params });

            viewElement = $(viewElement);

            const view = viewElement.attr("data-view");
            params.view = view;
            
            const queryString = createQueryString(params);
            const currentRoute = window.location.pathname.replace(window.baseUri, "");
            const resp = await viewEndpoint.get(`/${currentRoute}/${queryString}`);
            const html = await resp.text();
            viewElement.prop("outerHTML", html);

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
