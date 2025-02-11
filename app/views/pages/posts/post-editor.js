const postId = document.currentScript.dataset.postId;

$(document).ready(function () {
    window.admin.post = {
        title: createPostTitleEditor(),
        text: createPostTextEditor()
    };

    const originalReset = window.admin.reset;
    const originalSave = window.admin.save;

    window.admin.reset = function () {
        originalReset();
        const { post } = window.admin;
        post.title.value = post.title.old;
        post.text.value = post.text.old;
    };

    window.admin.save = async function () {
        const { baseUrl, api, admin, views } = window;
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

            window.history.pushState(null, "", `${baseUrl}/posts/${updatedPost.slug}`);
            await views.postArticle.reload();

            admin.post.title = createPostTitleEditor();
            admin.post.text = createPostTextEditor();
        }

        madeChanges = madeChanges || madePostChanges;

        return madeChanges;
    };

    function createPostTitleEditor() {
        return createController('[data-post-field="title"]');
    }

    function createPostTextEditor() {
        const { createTextEditor } = window.functions;
        const editor = createTextEditor('[data-post-field="text"]');
        return editor;
    }
});
