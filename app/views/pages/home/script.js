$(document).ready(function () {
    const { views } = window;
    const { getQueryParams } = window.functions;

    let query = getQueryParams();
    let page = query.page;
    let size = query.size;

    $(window).on("scroll", () => {
        const infiniteLoaderDetector = $("#infinite-loader-detector");
        const visible = infiniteLoaderDetector.is(":visible");
        if (!visible)
            return;

        query = getQueryParams();

        if (!page)
            page = 0;
        else
            page++;

        if (!size)
            size = 5;

        query.page = page;
        query.size = size;

        console.log("query", query);

        views.postList.reload(query);
    });
});