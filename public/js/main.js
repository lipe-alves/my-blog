(() => {
    const currentScript = $(document.currentScript);
    const apiUrl = currentScript.data("api-url");

    // Initialize 
    window.myBlog = {
        apiUrl
    };
})();
