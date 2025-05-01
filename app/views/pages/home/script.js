$(document).ready(function () {
    const { views } = window;
    const { getQueryParams } = window.functions;

    let query = getQueryParams();
    let page = query.page;
    let size = query.size;

    $(window).on("scroll", () => {
        console.clear();
        const infiniteLoaderDetector = $("#infinite-loader-detector")[0];
        const rect = infiniteLoaderDetector.getBoundingClientRect();

        const visible = rect.y <= window.innerHeight;
        if (!visible)
            return;

        query = getQueryParams();

        if (!page)
            page = 0;
        else
            page++;

        if (!size)
            size = 5;

        query.page = 0;
        query.size = (page + 1) * size;

        console.log("query", query);

        views.postList.reload(query);
    });
});