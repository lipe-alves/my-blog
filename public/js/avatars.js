(() => {
    function generateAvatarColors() {
        $('[data-avatar="true"]').each(function () {
            const {
                stringToColor,
                removeWhitespaces,
                removeNewlines
            } = window.myBlog.functions;

            let text = $(this).text();
            text = removeNewlines(text);
            text = removeWhitespaces(text);
            const color = stringToColor(text);

            $(this).css("background-color", color);
        });
    }

    window.myBlog.avatars = {
        generateAvatarColors
    };
})();