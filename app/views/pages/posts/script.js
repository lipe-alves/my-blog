async function handleSendComment(form, evt) {
    evt.preventDefault();

    const { api, toast, views } = window;
    const { delayAsync } = window.functions;

    form = $(form);
    const button = form.find('[type="submit"]');
    const fieldset = form.find("fieldset");

    const postId = form.find('[name="post_id"]').val();
    const replyTo = form.find('[name="reply_to"]')?.val() || null;
    const fullname = form.find('[name="fullname"]').val();
    const email = form.find('[name="email"]').val();
    const comment = form.find('[name="comment"]').val();

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        button.prop("disabled", disabled);
        button[disabled ? "addClass" : "removeClass"]("is-loading");
        fieldset.prop("disabled", disabled);
    };

    const sendComment = async () => {
        await api.comments.send({
            post_id: postId,
            reply_to: replyTo,
            comment,
            fullname,
            email
        });

        await views.commentList.reload();

        form[0].reset();
    };

    try {
        setFormDisabled(true);
        await delayAsync(sendComment, 3000);
        toast.success("Comentário enviado com sucesso");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}

/** @param {string} commentId */
async function handleOpenReplyForm(commentId) {
    const commentCard = $(`[data-comment-id="${commentId}"]`);
    const commentReplies = commentCard.find(".CommentCard-replies");

    
}