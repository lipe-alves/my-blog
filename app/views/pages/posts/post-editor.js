$(document).ready(function () {
    const { createTextEditor } = window.functions;
    const editor = createTextEditor(".Post-text");
    window.admin.post = editor;
});
