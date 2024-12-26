(() => {
    /**
     * @typedef ToastConfig
     * @type {{
     *      message: string;
     *      type: "primary" | "link" | "info" | "success" | "warning" | "danger";
     *      duration: number;
     *      position: "top-left" | "top-center" | "top-right" | "center" | "bottom-left" | "bottom-center" | "bottom-right";
     * }}
     */

    window.myBlog.toast = {
        /** @param {ToastConfig} config */
        show(config) {
            const {
                message,
                type = "primary",
                duration = 2000,
                position = "top-center",
                ...rest
            } = config;

            bulmaToast.toast({
                message,
                type: `Toast is-${type}`,
                duration,
                position,
                ...rest
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        success(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "success"
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        error(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "danger"
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        info(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "info"
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        warning(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "warning"
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        default(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "primary"
            });
        },
        /**
         * @param {string} message
         * @param {ToastConfig=} config
         */
        link(message, config = {}) {
            this.show({
                ...config,
                message,
                type: "link"
            });
        }
    };
})();
