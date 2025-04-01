(() => {
    const { admin } = window;
    const { createController, createTextEditor, removeWhitespaces, removeNewlines } = window.functions;
    const postId = document.currentScript.dataset.postId;

    class PostCategoryEditor {
        get select() {
            return $("#category-select")[0];
        }

        get options() {
            return Array.from(this.select.querySelectorAll("option"));
        }

        get selected() {
            const id = this.select.value;
            let name = $(this.options.find(option => option.value === id)).text();
            name = removeNewlines(name);
            name = removeWhitespaces(name);
            return { id, name };
        }

        /** @param {string} categoryId */
        delete(categoryId) {
            const selector = `[data-post-category-id="${categoryId}"]`;
            const a = $(selector);
            a.attr("data-deleted", true);

            for (const option of this.options) {
                if (option.value == categoryId) {
                    $(option).attr("disabled", false);
                }
            }
        }

        /** @param {string} categoryId */
        undelete(categoryId) {
            const selector = `[data-post-category-id="${categoryId}"]`;
            const a = $(selector);
            a.attr("data-deleted", false);

            for (const option of this.options) {
                if (option.value == categoryId) {
                    $(option).attr("disabled", true);
                }
            }
        }

        /**
         * @param {string} categoryId 
         * @param {string} categoryName 
         */
        add(categoryId, categoryName) {

            for (const option of this.options) {
                if (option.value == categoryId) {
                    $(option).attr("disabled", true);
                }
            }
        }

        static create() {
            return new PostCategoryEditor();
        }
    }

    admin.post = {
        title: createPostTitleEditor(),
        text: createPostTextEditor(),
        categories: PostCategoryEditor.create()
    };

    const originalReset = admin.reset;
    const originalSave = admin.save;

    admin.reset = function () {
        originalReset();
        const { post } = admin;
        post.title.value = post.title.old;
        post.text.value = post.text.old;
    };

    admin.save = async function () {
        const { baseUrl, api, admin, views } = window;
        let madeChanges = await originalSave();

        const postUpdates = {};

        for (const [key, controller] of Object.entries(admin.post)) {
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

    window.admin = admin;

    function createPostTitleEditor() {
        return createController('[data-post-field="title"]');
    }

    function createPostTextEditor() {
        return createTextEditor('[data-post-field="text"]');
    }
})();