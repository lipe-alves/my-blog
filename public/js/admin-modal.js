$(document).ready(async function () {
    window.modal.show({
        title: "Autentique-se como administrador",
        content: `
            <div class="">
                <input class="input is-fullwidth"
            </div>
        `,
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
