$(document).ready(function () {
    $('[data-filter-type="category"]').each(function () {
        $(this).on("click", async function () {
            const categoryId = $(this).data("category-id");
            setSearchFilters({ categoryId });
            searchPosts();
        });
    });

    /**
     * @param {{
     *     categoryId?: number;
     * }} params
     * @returns {Promise<void>} 
     */
    function setSearchFilters(params) {
        const { getQueryParams, setQueryParams } = window.myBlog.functions;
        const { categoryId } = params;

        const query = getQueryParams();
        query.category = categoryId;

        setQueryParams(query);
    }

    async function searchPosts() {
        const { getQueryParams } = window.myBlog.functions;
        const query = getQueryParams();
    }
});
