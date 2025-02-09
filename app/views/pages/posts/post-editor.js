const postId = document.currentScript.dataset.postId;

$(document).ready(function () {
    const titleElement = $('[data-post-field="title"]');
    const postTitle = createController(titleElement);
    const textEditor = createPostTextEditor();

    window.admin.post = {
        title: postTitle,
        text: textEditor
    };

    const originalReset = window.admin.reset;
    const originalSave = window.admin.save;

    window.admin.reset = function () {
        originalReset();
        postTitle.value = postTitle.old;
        textEditor.value = textEditor.old;
    };

    window.admin.save = async function () {
        const { api } = window;
        let madeChanges = await originalSave();

        const postUpdates = {};

        for (const [key, controller] of Object.entries(window.admin.post)) {
            if (controller.value !== controller.old) {
                postUpdates[key] = controller.value;
            }
        }

        const madePostChanges = Object.keys(postUpdates).length > 0;
        if (madePostChanges) {
            const updatedPost = await api.posts.update(postId, postUpdates);

            window.history.pushState(null, "", `${window.baseUrl}/posts/${updatedPost.slug}`);
            await window.views.postArticle.reload();

            const editor = createPostTextEditor();
            window.admin.post.text = editor;
        }

        madeChanges = madeChanges || madePostChanges;

        return madeChanges;
    };

    function createPostTextEditor() {
        const { createTextEditor } = window.functions;
        const postTextSelector = '[data-post-field="text"]';
        const editor = createTextEditor(postTextSelector);
        return editor;
    };
});
