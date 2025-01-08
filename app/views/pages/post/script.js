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

        await api.comments.send({
            post_id: postId,
            comment,
            fullname,
            email
        });

        form[0].reset();

        toast.success("Comentário enviado com sucesso");
    } catch (err) {
        toast.error(err.message);
    } finally {
        await commentsLoader.hide();
    }
}

$(document).ready(function () {
    const { avatars } = window.myBlog;
    avatars.generateAvatarColors();
});