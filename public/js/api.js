(() => {
    const { apiUrl, baseUri } = window.myBlog;
    const { createEndpoint, createQueryString } = window.myBlog.functions;
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

        views: {
            async postList(params) {
                const queryString = createQueryString(params);
                const resp = await viewEndpoint.get(`/post-list/${queryString}`);
                return resp.text();
            }
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

    window.myBlog.api = api;
})();
