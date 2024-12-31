const searchInput = $("#search-bar");

function handleRedirectToHome() {
    const { baseUri } = window.myBlog;
    window.location.href = `${baseUri}/?search=${searchInput.val()}`;
}

async function handleSearch() {
    handleRedirectToHome();
}

function handleSearchOnEnter(evt) {
    const { onEnterPress } = window.myBlog.functions;
    onEnterPress(evt, handleRedirectToHome);
}
