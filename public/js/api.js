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
                const resp = await apiEndpoint.request(
                    `/posts/${queryString}`,
                    {
                        method: "GET"
                    }
                );
                return resp.json();
            }
        }
    };

    window.myBlog.api = api;
})();