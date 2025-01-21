$(document).ready(async function () {
    window.modal.show({
        title: "Autentique-se como administrador",
        content: ``,
        footer: `
            <div class="buttons">
                <button class="button" onclick="handleCancelAuth()">
                    Cancelar
                </button>
                <button class="button is-success" onclick="handleAuth()">
                    Autenticar-se
                </button>
            </div>
        `
    });
});
