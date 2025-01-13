const searchInput = $("#search-bar");

function handleRedirectToHome() {
    const { baseUrl } = window;
    window.location.href = `${baseUrl}/?search=${searchInput.val()}`;
}

async function handleSearch() {
    handleRedirectToHome();
}

function handleSearchOnEnter(evt) {
    const { onEnterPress } = window.functions;
    onEnterPress(evt, handleRedirectToHome);
}
