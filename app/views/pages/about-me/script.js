(() => {
    const { createTextEditor } = window.functions;
    const editor = createTextEditor('[data-settings="about_me"]');
    console.log("editor", editor);
})();
