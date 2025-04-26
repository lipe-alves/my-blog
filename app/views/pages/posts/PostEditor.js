class PostEditor {
    #data;
    #title;
    #text;
    #categories;

    /** @param {any} post */
    constructor(post) {
        this.#data = post;
        this.#mount();
    }

    get data() {
        return this.#data;
    }

    get title() {
        return this.#title;
    }

    get text() {
        return this.#text;
    }

    get categories() {
        return this.#categories;
    }

    #createPostTitleEditor() {
        const { createController } = window.functions;
        return createController('[data-post-field="title"]');
    }

    #createPostTextEditor() {
        const { createTextEditor } = window.functions;
        return createTextEditor('[data-post-field="text"]');
    }

    async #updatePost() {
        const { api } = window;
        const postUpdates = {};

        if (this.title.value !== this.title.old)
            postUpdates.title = this.title.value;

        if (this.text.value !== this.text.old)
            postUpdates.text = this.text.value;

        postUpdates.categories = this.categories.list.map(category => category.id).join(",");

        const madePostChanges = Object.keys(postUpdates).length > 0;

        if (madePostChanges) {
            const updatedPost = await api.posts.update(this.data.id, postUpdates);
            await this.#update(updatedPost);
        }

        return madePostChanges;
    }

    async #createPost() {
        const { api } = window;
        const postData = {};

        postData.title = this.title.value;
        postData.text = this.text.value;
        postData.categories = this.categories.list.map(category => category.id).join(",");

        const post = await api.posts.create(postData);
        await this.#update(post);

        return post;
    }

    #mount() {
        const post = this.#data;

        this.#title = this.#createPostTitleEditor();
        this.#text = this.#createPostTextEditor();
        this.#categories = CategoryEditor.create();

        const publishButton = $("#publish-post-button");
        publishButton.css("display", post.published === "1" ? "none" : "");
    }

    async #update(post) {
        const { history, views, baseUrl } = window;

        history.pushState(null, "", `${baseUrl}/posts/${post.slug}`);
        await views.postArticle.reload();

        this.#data = post;
        this.#mount();
    }

    async savePost() {
        let madeChanges = false;
        const isNewPost = this.data.id === "new";

        if (isNewPost) {
            await this.#createPost();
            madeChanges = true;
        } else {
            madeChanges = await this.#updatePost();
        }

        return madeChanges;
    }

    async publishPost() {
        const { api } = window;
        const postId = this.data.id;
        const post = await api.posts.publish(postId);
        this.#update(post);
    }

    /** @param {any} post */
    static create(post) {
        return new PostEditor(post);
    }
}