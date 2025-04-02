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

        views: {
            /** 
             * @param {string} viewName
             * @param {HTMLElement} parent
             * @param {{ [key: string]: any }} params
             * @returns {string}
             */
            async render(viewName, parent, params = {}) {
                parent = $(parent);

                params.view = viewName;

                const baseUrl = window.location.href.replace(window.location.search, "");
                const viewEndpoint = createEndpoint(baseUrl);
                const queryString = createQueryString(params);

                const resp = await viewEndpoint.get(`/${queryString}`);
                let html = await resp.text();
                html = removeDuplicateDependencies(html);

                parent.html(html);

                return html;
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
                let html = await resp.text();
                html = removeDuplicateDependencies(html);

                parent = parent ? $(parent) : viewElement;
                parent.prop("outerHTML", html);

                return html;
            },

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

        media: {
            /**
             * @param {string} path 
             * @param {string} newName 
             */
            async rename(path, newName) {
                const resp = await apiEndpoint.patch(`/media/?path=${path}`, { name: newName });
                const data = resp.json();
                return data;
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

    /** @param {string} html */
    function removeDuplicateDependencies(html) {
        const pseudo = document.createElement("div");
        pseudo.innerHTML = html;

        const viewLinks = Array.from(pseudo.querySelectorAll("link"));
        const docLinks = Array.from(document.querySelectorAll("link"));

        for (const viewLink of viewLinks) {
            const viewHref = viewLink.getAttribute("href").split("?v=")[0];

            const alreadyExists = docLinks.some(docLink => {
                const docHref = docLink.getAttribute("href").split("?v=")[0];
                return docHref === viewHref;
            });

            if (!alreadyExists) {
                includeStyle(viewLink);
            }

            const parent = viewLink.parentNode;
            parent.removeChild(viewLink);
        }

        const viewScripts = Array.from(pseudo.querySelectorAll("script"));
        const docScripts = Array.from(document.querySelectorAll("script"));

        for (const viewScript of viewScripts) {
            const viewSrc = viewScript.getAttribute("src").split("?v=")[0];

            const alreadyExists = docScripts.some((docScript) => {
                const docSrc = docScript.getAttribute("src").split("?v=")[0];
                return docSrc === viewSrc;
            });

            if (!alreadyExists) {
                includeScript(viewScript);
            }

            const parent = viewScript.parentNode;
            parent.removeChild(viewScript);
        }

        return pseudo.innerHTML;
    }

    /** @param {HTMLLinkElement} link */
    function includeStyle(link) {
        document.querySelector("head").innerHTML += link.outerHTML;
    }

    /** @param {HTMLScriptElement} script */
    function includeScript(script) {
        const clone = document.createElement("script");
        
        for (const attr of Array.from(script.attributes)) {
            clone[attr.name] = attr.value;
        }

        clone.onload = console.log;
        clone.onerror = console.error;
        
        document.body.appendChild(clone);
    }
})();
