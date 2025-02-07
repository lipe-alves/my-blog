const postId = document.currentScript.dataset.postId;

$(document).ready(function () {
    const { createTextEditor } = window.functions;
    
    const postTitle = createController($('[data-post-field="title"]'));
    const textEditor = createTextEditor('[data-post-field="text"]');

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
            await api.posts.update(postId, postUpdates);
        }

        madeChanges = madeChanges || madePostChanges;

        return madeChanges;
    };
});
