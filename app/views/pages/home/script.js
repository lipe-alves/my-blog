$(document).ready(function () {
    attachFilterEventListeners();

    function attachFilterEventListeners() {
        $("[data-filter-key]").each(function () {
            $(this).on("click", async function () {
                const key = $(this).data("filter-key");
                const value = $(this).data("filter-value");
                setFilterParams({ [key]: value });
                await filterPosts();
            });
        });
    }

    /**
     * @param {{
     *     category_id?: number;
     *     category_name?: string;
     *     page?: number;
     *     size?: number;
     * }} params
     */
    function setFilterParams(params) {
        const { getQueryParams, setQueryParams } = window.myBlog.functions;
        const query = getQueryParams();

        for (const [key, value] of Object.entries(params)) {
            query[key] = value;
        }

        setQueryParams(query);
    }

    async function filterPosts() {
        const { api } = window.myBlog;
        const { getQueryParams } = window.myBlog.functions;

        const query = getQueryParams();

        if (!query.page) query.page = 0;
        if (!query.size) query.size = 10;

        query.columns = "p.*,category_names";

        const posts = await api.posts.search(query);

        renderPosts(posts);
    }

    function renderPosts(posts) {
        const postCardTemplate = $('.PostCard[data-template="true"]');
        const postListElement = postCardTemplate.parent();

        postListElement.find('li[data-template="false"]').each(function () {
            $(this).remove();
        });

        console.log("posts", posts);

        for (const post of posts) {
            const postCard = postCardTemplate.clone();

            postCard.data("post-id", post.id);
            postCard.data("template", "false");

            for (const [column, value] of Object.entries(post)) {
                postCard.find(`[data-column="${column}"]`).html(value);
            }

            console.log(postCard.html());

            postListElement.append(postCard);
        }

        attachFilterEventListeners();
    }
});
