const searchInput = $("#search-bar");

function handleRedirectToHome() {
    const { baseUri } = window;
    window.location.href = `${baseUri}/?search=${searchInput.val()}`;
}

async function handleSearch() {
    handleRedirectToHome();
}

function handleSearchOnEnter(evt) {
    const { onEnterPress } = window.functions;
    onEnterPress(evt, handleRedirectToHome);
}
