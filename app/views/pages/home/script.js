async function handleApplyFilter(element) {
    const { getQueryParams } = window.myBlog.functions;

    const query = getQueryParams();
    const key = $(element).data("filter-key");
    const value = $(element).data("filter-value");
    let params = { [key]: value };

    if (query[key] === value) {
        params[key] = "";
    }

    setFilterParams(params);
    await filterPosts();
}

function handleSearch(evt) {
    const { onEnterPress } = window.myBlog.functions;
    const searchInput = $(evt.target);

    onEnterPress(evt, async function () {
        setFilterParams({ search: searchInput.val() });
        await filterPosts();
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
        if (!value) {
            delete query[key];
            continue;
        }

        query[key] = value;
    }

    setQueryParams(query);
}

async function filterPosts() {
    const { api, toast } = window.myBlog;
    const { getQueryParams } = window.myBlog.functions;
    const { postsLoader } = window.myBlog.loaders;

    const query = getQueryParams();
    const hasQuery = Object.keys(query).length > 0;
    if (!hasQuery) return;

    if (!query.page) query.page = 0;
    if (!query.size) query.size = 10;

    query.columns = "p.*,category_names";

    try {
        postsLoader.show();

        const resp = await api.posts.search(query);
        const posts = resp.list;

        renderPosts(posts);
    } catch (err) {
        toast.error(err.message);
    } finally {
        postsLoader.hide();
    }
}

function renderPosts(posts) {
    const postCardTemplate = $('.PostCard[data-template="true"]');
    const postListElement = postCardTemplate.parent();

    postListElement.find('li[data-template="false"]').each(function () {
        $(this).remove();
    });

    for (const post of posts) {
        const postCard = postCardTemplate.clone();

        postCard.attr("data-post-id", post.id);
        postCard.attr("data-template", "false");

        for (let [column, value] of Object.entries(post)) {
            const columnElement = postCard.find(`[data-column="${column}"]`);

            if (column === "created_at") {
                value = new Date(value).toLocaleString();
            } else if (column === "category_names") {
                const categories = value.split(",").map(c => c.trim());
                const a = columnElement.find("a");
                value = [];

                for (const category of categories) {
                    a.attr("data-filter-value", category);
                    a.html(category);
                    value.push(a.prop("outerHTML"));
                }

                value = value.join(", ");
            }

            columnElement.html(value);
        }

        postListElement.append(postCard);
    }
}

$(document).ready(function () {
    filterPosts();
});
