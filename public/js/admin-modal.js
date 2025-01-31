$(document).ready(async function () {
    window.modal.show({
        title: "Autentique-se como administrador",
        content: `
            <input 
                id="password-input"
                class="input is-fullwidth" 
                name="password" 
                placeholder="Digite a senha..."
            >
        `,
        buttons: [
            `<button
                id="cancellation-btn"
                class="button" 
                onclick="handleCancelAuth()"
            >
                Cancelar
            </button>`,
            `<button 
                id="authentication-btn"
                class="button is-success" 
                onclick="handleSubmitCredentials()"
            >
                Autenticar-se
            </button>`
        ],
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
    const { clearQueryParams, delayAsync } = window.functions;

    const submitButton = $("#authentication-btn");
    const cancellationButton = $("#cancellation-btn");
    const passwordInput = $("#password-input");

    /** @param {boolean} disabled */
    const setFormDisabled = (disabled) => {
        submitButton.prop("disabled", disabled);
        cancellationButton.prop("disabled", disabled);
        submitButton.toggleClass("is-loading");
        passwordInput.prop("disabled", disabled);
    };

    const authenticate = async () => {
        const password = passwordInput.val();
        await api.admin.authenticate(password);

        clearQueryParams();
        window.location.reload();
    };

    try {
        setFormDisabled(true);
        await delayAsync(authenticate, 3000);
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}
