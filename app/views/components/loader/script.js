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
            loader.attr("data-visible", "false");
        }
    };
})();