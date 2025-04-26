(() => {
    const { admin } = window;
    
    const post = {...document.currentScript.dataset};
    admin.post = PostEditor.create(post);

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
        const postSaved = await admin.post.savePost();
        return madeChanges || postSaved;
    };

    window.admin = admin;
})();
