const searchInput = $("#search-bar");

async function handleApplyFilter(element) {
    const { getQueryParams } = window.myBlog.functions;

    const query = getQueryParams();
    const key = $(element).data("filter-key");
    const value = $(element).data("filter-value");
    let params = { [key]: value };

    const alreadySelected = query[key] === value;
    if (alreadySelected) {
        params[key] = "";
    }

    setFilterParams(params);
    await filterPosts();
}

async function handleSearch() {
    setFilterParams({ search: searchInput.val() });
    await filterPosts();
}

function handleSearchOnEnter(evt) {
    const { onEnterPress } = window.myBlog.functions;
    onEnterPress(evt, handleSearch);
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
    updateFilterIndicators();
}

async function filterPosts() {
    const { api, toast } = window.myBlog;
    const { getQueryParams } = window.myBlog.functions;
    const { postsLoader } = window.myBlog.loaders;

    const query = getQueryParams();

    if (!query.page) query.page = 0;
    if (!query.size) query.size = 10;

    query.columns = "p.*,category_names";

    try {
        postsLoader.show();

        const resp = await api.posts.search(query);
        const posts = resp.list;

        await postsLoader.hide();

        renderPosts(posts);
    } catch (err) {
        await postsLoader.hide();
        toast.error(err.message);
    }
}

function updateFilterIndicators() {
    const { getQueryParams } = window.myBlog.functions;

    const query = getQueryParams();
    $('[data-filter-key="category"]').each(function () {
        const categoryValue = $(this).data("filter-value");
        const active = categoryValue === query.category;
        if (active) {
            $(this).addClass("active");
        } else {
            $(this).removeClass("active");
        }
    });

    if (query.search) {
        searchInput.val(query.search);
    }

    const filterTitle = $("#filter-title");
    const noFilters = !Object.values(query).some(Boolean);
    filterTitle.attr("data-visible", String(!noFilters));

    let filterMsg = "Posts encontrados";

    if (query.category) {
        filterMsg += ` em "${query.category}"`
    }

    if (query.search) {
        filterMsg += ` com o texto "${query.search}"`
    }

    filterMsg += "...";

    filterTitle.html(filterMsg);
}

function renderPosts(posts) {
    const postCardTemplate = $('.PostCard[data-template="true"]');
    const postListElement = postCardTemplate.parent();

    postListElement.find('li[data-template="false"]').each(function () {
        $(this).remove();
    });

    for (const post of posts) {
        let postCard = postCardTemplate.clone();
        postCard.attr("data-template", "false");
        let postCardHtml = postCard.prop("outerHTML");

        for (let [column, value] of Object.entries(post)) {
            if (column === "created_at") {
                value = new Date(value).toLocaleString();
            } else if (column === "category_names") {
                const categories = value.split(",").map(c => c.trim());
                const a = postCard.find('[data-filter-key="category"]');
                value = [];

                for (const categoryName of categories) {
                    let aHtml = a.prop("outerHTML");
                    aHtml = aHtml.replaceAll(":category_name", categoryName);
                    value.push(aHtml);
                }

                value = value.join(", ");
            }

            if (column !== "category_names") {
                postCardHtml = postCardHtml.replaceAll(`:${column}`, value);
            } else {
                const a = postCard.find('[data-filter-key="category"]');
                postCardHtml = postCardHtml.replace(a.prop("outerHTML"), value);
            }
        }

        postListElement.append(postCard);
        postCard.prop("outerHTML", postCardHtml);
    }

    const noPostsMessage = $("#no-posts-message");
    noPostsMessage.attr("data-visible", String(posts.length === 0));
}

$(document).ready(function () {
    const { getQueryParams } = window.myBlog.functions;
    const query = getQueryParams();

    updateFilterIndicators();
    
    const hasQuery = Object.keys(query).length > 0;
    if (!hasQuery) return;

    filterPosts();
});
