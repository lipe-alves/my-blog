(() => {
    class TextEditor extends Quill {
        /** @property {string} old */
        old;

        /** @param {string} selector */
        constructor(selector) {
            super(selector, { ...TextEditor.EDITOR_CONFIG });
            this.old = this.value;
        }

        static get EDITOR_CONFIG() {
            return {
                modules: {
                    toolbar: {
                        container: [
                            ["bold", "italic", "underline", "strike"],
                            ["blockquote", "code-block"],
                            ["link", "image", "video", "formula"],

                            [{ "list": "ordered" }, { "list": "bullet" }, { "list": "check" }],
                            [{ "script": "sub" }, { "script": "super" }],

                            [{ "indent": "-1" }, { "indent": "+1" }],
                            [{ "direction": "rtl" }],

                            [{ "size": ["small", false, "large", "huge"] }],
                            [{ "header": [1, 2, 3, 4, 5, 6, false] }],

                            [{ "color": [] }, { "background": [] }],
                            [{ "font": [] }],
                            [{ "align": [] }],

                            ["clean"]
                        ],
                        handlers: {
                            video: TextEditor.toolbarHandler(TextEditor.uploadVideo),
                            image: TextEditor.toolbarHandler(TextEditor.uploadImage)
                        }
                    }
                },
                theme: "snow"
            };
        }

        /** @param {(editor: TextEditor) => void} handler */
        static toolbarHandler(handler) {
            return function () {
                handler(this.quill);
            };
        }

        async uploadVideo() {
            TextEditor.uploadVideo(this);
        }

        async uploadImage() {
            TextEditor.uploadImage(this);
        }

        /** @param {TextEditor} editor */
        static async uploadImage(editor) {
            const { admin } = window;

            let selection = editor.getSelection();
            let index = selection?.index || 0;

            await admin.mediaLibrary.show({
                accept: "image/*",
                multiple: true
            });

            admin.mediaLibrary.addEventListener("send-files", (files) => {
                admin.mediaLibrary.hide();

                for (const file of files) {
                    editor.insertEmbed(index, "image", file.src);
                    
                    selection = editor.getSelection();
                    index = selection?.index || 0;
                }
            });
        }

        /** @param {TextEditor} editor */
        static async uploadVideo(editor) {
            const { admin } = window;

            let selection = editor.getSelection();
            let index = selection?.index || 0;

            await admin.mediaLibrary.show({
                accept: "video/*",
                multiple: true
            });

            admin.mediaLibrary.addEventListener("send-files", (files) => {
                admin.mediaLibrary.hide();

                for (const file of files) {
                    editor.insertEmbed(index, "image", file.src);
                    
                    selection = editor.getSelection();
                    index = selection?.index || 0;
                }
            });
        }

        get html() {
            return this.root.innerHTML;
        }

        get value() {
            return this.html;
        }

        /** @param {string} html */
        set value(html) {
            this.clipboard.dangerouslyPasteHTML(html);
        }

        get text() {
            return this.getText();
        }

        /** @param {string} selector */
        static create(selector) {
            return new TextEditor(selector);
        }
    }

    window.functions.createTextEditor = TextEditor.create;
})();
