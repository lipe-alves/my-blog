(() => {
    const currentScript = $(document.currentScript);
    const loaderId = currentScript.data("loader-id");
    const loader = $(`#${loaderId}`);

    if (!window.myBlog.loaders) {
        window.myBlog.loaders = {};
    }

    window.myBlog.loaders[loaderId] = {
        show() {
            loader.attr("data-visible", "true");
        },
        hide() {
            setTimeout(() => loader.attr("data-visible", "false"), 1000);
        }
    };
})();
