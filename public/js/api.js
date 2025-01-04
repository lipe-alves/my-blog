(() => {
    const { apiUrl } = window.myBlog;
    const { createEndpoint, createQueryString } = window.myBlog.functions;
    const apiEndpoint = createEndpoint(apiUrl);

    const api = {
        endpoint: apiEndpoint,

        /** @param {RequestInit=} config */
        async ping(config = {}) {
            const resp = await this.endpoint.request("/ping", {
                ...config,
                method: "GET"
            });
            return resp.json();
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
             *     text: string;
             *     reply_comment_id?: string;
             *     reader_fullname?: string;
             *     reader_photo?: string;
             *     reader_email: string;
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