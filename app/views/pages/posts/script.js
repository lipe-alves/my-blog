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
        button.toggleClass("is-loading");
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
function handleOpenReplyForm(commentId) {
    const commentCards = $("[data-comment-id]");

    commentCards.each(function () {
        const id = $(this).attr("data-comment-id");
        const visible = id === commentId;
        const replyForm = $(this).find(`#comment-form-${id}`);
        replyForm.attr("data-visible", String(visible));
    });
}

/** @param {string} formId */
function handleCloseReplyForm(formId) {
    const form = $(`#${formId}`);
    form.attr("data-visible", "false");
}

/** @param {string} categoryId */
function handleRemoveCategory(categoryId) {
    window.admin.post.categories.delete(categoryId);
}

function handleAddCategory() {
    const { id, name } = window.admin.post.categories.selected;
    window.admin.post.categories.add(id, name);
}
