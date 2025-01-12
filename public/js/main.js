(() => {
    const currentScript = $(document.currentScript);
    const apiUrl = currentScript.data("api-url");
    const baseUri = currentScript.data("base-url");

    // Initialize 
    window.apiUrl = apiUrl;
    window.baseUri = baseUri;
})();
