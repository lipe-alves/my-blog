/**
 * @typedef LoaderParams 
 * @type {{
 *     id: string;
 *     wrapperWidth: string;
 *     wrapperHeight: string;
 *     loaderWidth: string;
 *     loaderHeight: string;
 * }}
 */

(() => {
    window.loader = {
        /**
         * @param {HTMLElement} parent 
         * @param {LoaderParams} params 
         * @returns {string}
         */
        show(parent = document.body, params = {}) {
            if (!containsLoader(parent)) {
                createLoader(parent, params);
            }

            const loader = $(getLoader(parent));
            loader.attr("data-visible", "true");

            return loader.attr("id");
        },
        /** @param {string} id */
        hide(id) {
            return new Promise(resolve => {
                setTimeout(() => {
                    const loader = $(`#${id}`);
                    if (!loader) return resolve();

                    loader.attr("data-visible", "false");
                    resolve()
                }, 1000);
            });
        }
    };

    /** 
     * @param {HTMLElement} parent
     * @param {LoaderParams} params 
     */
    function createLoader(parent, params) {
        const { generateId } = window.functions;
        const {
            id = generateId(),
            wrapperHeight = "inherit",
            wrapperWidth = "inherit",
            loaderHeight = "inherit",
            loaderWidth = "inherit"
        } = params;

        $(parent).append(`
           <div
               id="${id}"
               class="Loader-wrapper"
               data-visible="false"
               style="width: ${wrapperWidth}; height: ${wrapperHeight};"
           >
               <div 
                   class="Loader is-loading" 
                   style="--loader-width: ${loaderWidth}; --loader-height: ${loaderHeight};"
               ></div>
           </div>
       `);
    }

    /** @param {HTMLElement} parent */
    function containsLoader(parent) {
        return !!getLoader(parent);
    }

    /** @param {HTMLElement} parent */
    function getLoader(parent) {
        return $(parent).find(".Loader-wrapper")[0];
    }
})();