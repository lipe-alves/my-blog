$(document).ready(function () {
    const { api } = window;

    if (!window.settings) {
        window.settings = {};
    }

    $("[data-settings]").each(function() {
        const settingsElement = $(this);
        settingsElement.attr("contenteditable", "true");
        
        const settings = settingsElement.attr("data-settings");

        let controller = {
            async save() {
                const newSettings = await api.settings.update({ [settings]: this.value });
                return newSettings;
            }
        };

        controller = new Proxy(controller, {
            get(target, prop) {
                if (prop === "value") {
                    return settingsElement.text();
                } else if (prop === "element") {
                    return settingsElement[0];
                }

                return target[prop];
            },
            set(target, prop, value) {
                if (prop === "value") {
                    settingsElement.text(value);
                } else if (prop === "element") {
                    return false;
                }

                return true;
            }
        });
        
        window.settings[settings] = controller;
    });

});
