/**
 * @param {{
 *     category_id?: number;
 *     category_name?: string;
 *     page?: number;
 *     size?: number;
 * }} params
 */
function setFilterParams(params) {
    const { getQueryParams, setQueryParams } = window.functions;
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

/**
 * @param {string} key 
 * @param {string} value 
 */
async function handleApplyFilter(key, value) {
    const { getQueryParams } = window.functions;

    const query = getQueryParams();
    let params = { [key]: value };

    const alreadySelected = query[key] === value;
    if (alreadySelected) {
        params[key] = "";
    }

    setFilterParams(params);

    await reloadFilters();
    await reloadPosts();
}

async function handleSearch(input) {
    await handleApplyFilter("search", input.value);
}

function handleSearchOnEnter(evt) {
    const { onEnterPress } = window.functions;
    onEnterPress(evt, (evt) => handleSearch(evt.target));
}

async function handleClearFilters() {
    const { clearQueryParams } = window.functions;
    clearQueryParams();

    await reloadFilters();
    await reloadPosts();
}

async function reloadPosts() {
    const { toast, views } = window;
    const { getQueryParams } = window.functions;

    const query = getQueryParams();
    const postList = views.postList;

    if (!query.page) query.page = 0;
    if (!query.size) query.size = 10;

    try {
        postList.loader.show();
        await postList.reload({ params: query });
        await postList.loader.hide();
    } catch (err) {
        await postList.loader.hide();
        toast.error(err.message);
    }
}

async function reloadFilters() {
    const { toast, views } = window;
    const { getQueryParams } = window.functions;

    const query = getQueryParams();

    try {
        await views.postFilters.reload({ params: query });
    } catch (err) {
        toast.error(err.message);
    }
}
