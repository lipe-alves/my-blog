$(document).ready(async function () {
    window.modal.show({
        title: "Autentique-se como administrador",
        content: `
            <input class="input is-fullwidth" name="password" placeholder="Digite a senha...">
        `,
        footer: `
            <div class="buttons">
                <button class="button" onclick="handleCancelAuth()">
                    Cancelar
                </button>
                <button class="button is-success" onclick="handleSubmitCredentials()">
                    Autenticar-se
                </button>
            </div>
        `,
        onHide: handleCancelAuth
    });
});

function handleCancelAuth() {
    const { clearQueryParams } = window.functions;
    clearQueryParams();
    window.location.reload();
}

async function handleSubmitCredentials() {
    const { api } = window;
    const { clearQueryParams, setQueryParams } = window.functions;

    try {
        const password = $('[name="password"]').val();
        await api.admin.authenticate(password);
        clearQueryParams();
        setQueryParams({ admin: true, password });
        window.location.reload();
    } catch (err) {
        toast.error(err.message);
    } finally {
        toast.error(err.message);
    }
}
