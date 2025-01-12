async function handleSendComment(form, evt) {
    evt.preventDefault();

    const { api, toast, views } = window;

    const button = $(evt.target);
    form = $(form);
    const fieldset = form.find("fieldset");

    const postId = form.find('input[name="post_id"]').val();
    const fullname = form.find('input[name="fullname"]').val();
    const email = form.find('input[name="email"]').val();
    const comment = form.find('textarea[name="comment"]').val();

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        button.prop("disabled", true);
        button[disabled ? "addClass" : "removeClass"]("is-loading");
        fieldset.prop("disabled", disabled);
    };

    try {
        setFormDisabled(true);

        await api.comments.send({
            post_id: postId,
            comment,
            fullname,
            email
        });

        await views.commentList.reload();

        form[0].reset();

        toast.success("Comentário enviado com sucesso");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}

$(document).ready(function () {
    const { avatars } = window;
    avatars.generateAvatarColors();
});