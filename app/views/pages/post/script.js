async function handleSendComment(form, evt) {
    evt.preventDefault();

    const { api, toast, loaders } = window.myBlog;
    const { commentsLoader } = loaders;
    form = $(form);

    const postId = form.find('input[name="post_id"]').val();
    const fullname = form.find('input[name="fullname"]').val();
    const email = form.find('input[name="email"]').val();
    const comment = form.find('textarea[name="comment"]').val();

    try {
        commentsLoader.show();

        const resp = await api.comments.send({
            post_id: postId,
            text: comment,
            reader_fullname: fullname,
            reader_email: email
        });

        toast.success(resp.message);
    } catch (err) {
        toast.error(err.message);
    } finally {
        await commentsLoader.hide();
    }

}