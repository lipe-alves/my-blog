(() => {

    class TextEditor extends Quill {
        /** @param {string} selector */
        constructor(selector) {
            super(selector, { ...TextEditor.EDITOR_CONFIG });
        }

        static get EDITOR_CONFIG() {
            return {
                modules: {
                    toolbar: [
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
                },
                theme: "snow"
            };
        }

        /** @param {string} selector */
        static create(selector) {
            return new TextEditor(selector);
        }
    }

    window.functions.createTextEditor = TextEditor.create;
})();
