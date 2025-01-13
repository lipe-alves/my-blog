async function handleSendComment(form, evt) {
    evt.preventDefault();

    const { api, toast, views } = window;
    const { delayAsync } = window.functions;

    form = $(form);
    const button = form.find('[type="submit"]');
    const fieldset = form.find("fieldset");

    const postId = form.find('[name="post_id"]').val();
    const fullname = form.find('[name="fullname"]').val();
    const email = form.find('[name="email"]').val();
    const comment = form.find('[name="comment"]').val();

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        button.prop("disabled", true);
        button[disabled ? "addClass" : "removeClass"]("is-loading");
        fieldset.prop("disabled", disabled);
    };

    try {
        setFormDisabled(true);

        await delayAsync(async () => {
            await api.comments.send({
                post_id: postId,
                comment,
                fullname,
                email
            });

            await views.commentList.reload();
        }, 3000);

        form[0].reset();

        toast.success("Comentário enviado com sucesso");
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}