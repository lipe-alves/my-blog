(() => {
    const currentScript = $(document.currentScript);
    const apiUrl = currentScript.data("api-url");
    const baseUrl = currentScript.data("base-url");

    // Initialize 
    window.apiUrl = apiUrl;
    window.baseUrl = baseUrl;
})();
