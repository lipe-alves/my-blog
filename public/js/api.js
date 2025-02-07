(() => {
    const { apiUrl } = window;
    const { createEndpoint, createQueryString } = window.functions;
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

        settings: {
            async update(updates) {
                const resp = await apiEndpoint.patch("/settings", updates);
                return resp.json();
            }
        },

        posts: {
            async search(params) {
                const queryString = createQueryString(params);
                const resp = await apiEndpoint.get(`/posts/${queryString}`);
                return resp.json();
            },

            /**
             * 
             * @param {string} id 
             * @param {{ [key: string]: any }} updates 
             */
            async update(id, updates) {
                const resp = await apiEndpoint.patch(`/posts/${id}`, updates);
                return resp.json();
            }
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
        },

        categories: {
            async search(params) {
                const queryString = createQueryString(params);
                const resp = await apiEndpoint.get(`/categories/${queryString}`);
                return resp.json();
            },

            /** 
             * @param {string} idOrName 
             * @param {string=} postsNewCategoryId
             */
            async delete(idOrName, postsNewCategoryId) {
                const resp = await apiEndpoint.delete(`/categories/${idOrName}`, {
                    data: {
                        posts_new_category_id: postsNewCategoryId
                    }
                });
                return resp.json();
            },

            /**
             * @param {{
             *     name: string;
             *     category_id? string;
             * }} data 
             */
            async create(data) {
                const resp = await apiEndpoint.post("/categories/", data);
                return resp.json();
            },

            async update(id, updates) {
                const resp = await apiEndpoint.patch(`/categories/${id}`, updates);
                return resp.json();
            }
        },

        admin: {
            /** @param {string} password */
            async login(password) {
                const resp = await apiEndpoint.post("/admin/login", { password });
                const data = resp.json();
                return data;
            },
            async logout() {
                const resp = await apiEndpoint.post("/admin/logout");
                const data = resp.json();
                return data;
            }
        }
    };

    window.api = api;
})();
