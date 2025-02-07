$(document).ready(async function () {
    window.modal.show({
        title: "Autentique-se como administrador",
        content: `
            <input 
                id="password-input"
                class="input is-fullwidth" 
                name="password" 
                placeholder="Digite a senha..."
                onkeypress="handleAuthOnEnter(event)"
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
                onclick="handleAuth()"
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

async function handleAuth() {
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

    const login = async () => {
        const password = passwordInput.val();
        await api.admin.login(password);

        clearQueryParams();
        window.location.reload();
    };

    try {
        setFormDisabled(true);
        await delayAsync(login, 3000);
    } catch (err) {
        toast.error(err.message);
    } finally {
        setFormDisabled(false);
    }
}

function handleAuthOnEnter(evt) {
    const { onEnterPress } = window.functions;
    onEnterPress(evt, handleAuth);
}
