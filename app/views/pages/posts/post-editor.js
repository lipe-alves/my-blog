(() => {
    const { baseUrl, api, admin } = window;
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

        get list() {
            const list = [];
            
            const selector = "[data-post-category-id]";
            $(selector).each(function () {
                const a = this;
                const { postCategoryId: id, postCategoryName: name, deleted } = a.dataset;
                if (deleted === "true") return;
                list.push({ id, name });
            });

            return list;
        }

        /** @param {string} categoryId */
        delete(categoryId) {
            const selector = `[data-post-category-id="${categoryId}"]`;
            const a = $(selector);

            const added = a.attr("data-added") === "true";
            
            if (added) {
                a.remove();
            } else {
                a.attr("data-deleted", true);
            }

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
            if (!categoryId || !categoryName) {
                return;
            }

            const postCategories = $(".Post-categories");
            const existing = postCategories.find(`[data-post-category-id="${categoryId}"]`);

            if (existing[0]) {
                existing.attr("data-deleted", false);
            } else {
                postCategories.append(`
                    <a
                        class="Post-category"
                        href="javascript:void(0)"
                        data-post-category-id="${categoryId}"
                        data-post-category-name="${categoryName}"
                        data-deleted="false"
                        data-added="true"
                    >
                        ${categoryName}
                        <button 
                            class="Post-category-delete" 
                            onclick="handleRemoveCategory('${categoryId}')"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </a>
                `);
            }

            for (const option of this.options) {
                if (option.value == categoryId) {
                    $(option).attr("disabled", true);
                }
            }
        }

        /** @param {string} categoryId */
        isDeleted(categoryId) {
            const selector = `[data-post-category-id="${categoryId}"]`;
            const a = $(selector);
            return a.attr("data-deleted") === "true";
        }

        reset() {
            const selector = "[data-post-category-id]";
            const context = this;

            $(selector).each(function () {
                const a = $(this);
                const added = a.attr("data-added") === "true";
                const deleted = a.attr("data-deleted") === "true";
                const { postCategoryId } = this.dataset;

                if (added) {
                    context.delete(postCategoryId);
                } else if (deleted) {
                    context.undelete(postCategoryId);
                }
            });
        }

        static create() {
            return new PostCategoryEditor();
        }
    }

    admin.post = {
        id: postId,
        title: createPostTitleEditor(),
        text: createPostTextEditor(),
        categories: PostCategoryEditor.create(),
        async save() {
            let madeChanges = false;
            const isNewPost = this.id === "new";

            if (isNewPost) {
                await createNewPost();
                madeChanges = true;
            } else {
                madeChanges = await savePostChanges();
            }

            return madeChanges;
        }
    };

    const originalReset = admin.reset;
    const originalSave = admin.save;

    admin.reset = function () {
        originalReset();
        const { post } = admin;
        post.title.value = post.title.old;
        post.text.value = post.text.old;
        post.categories.reset();
    };

    admin.save = async function () {
        const madeChanges = await originalSave();
        const postSaved = await admin.post.save();
        return madeChanges || postSaved;
    };

    window.admin = admin;

    function createPostTitleEditor() {
        return createController('[data-post-field="title"]');
    }

    function createPostTextEditor() {
        return createTextEditor('[data-post-field="text"]');
    }

    async function savePostChanges() {
        const postUpdates = {};

        for (const [key, controller] of Object.entries(admin.post)) {
            if (key === "categories") {
                postUpdates.categories = controller.list.map(category => category.id).join(",");
            } else if (controller.value !== controller.old) {
                postUpdates[key] = controller.value;
            }
        }

        const madePostChanges = Object.keys(postUpdates).length > 0;

        if (madePostChanges) {
            const updatedPost = await api.posts.update(postId, postUpdates);
            await syncPostEditor(updatedPost);
        }

        return madePostChanges;
    }

    async function createNewPost() {
        const postData = {};

        for (const [key, controller] of Object.entries(admin.post)) {
            if (key === "categories") {
                postData.categories = controller.list.map(category => category.id).join(",");
            } else {
                postData[key] = controller.value;
            }
        }

        const post = await api.posts.create(postData);
        await syncPostEditor(post);

        return post;
    }

    async function syncPostEditor(post) {
        const { views } = window;
        
        window.history.pushState(null, "", `${baseUrl}/posts/${post.slug}`);
        await views.postArticle.reload();

        admin.post.id = post.id;
        admin.post.title = createPostTitleEditor();
        admin.post.text = createPostTextEditor();
        admin.categories = PostCategoryEditor.create();
    }
})();
