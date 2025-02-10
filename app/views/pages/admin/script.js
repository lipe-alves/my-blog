const iframe = document.querySelector("#admin-window");
let iframeHref = null;

iframe.addEventListener("load", () => {
    setInterval(() => {
        const iframeWindow = iframe.contentWindow;
        
        if (!window.functions) {
            window.functions = {};
        }

        const { baseUrl } = iframeWindow;

        const getQueryParams = cloneFunction(iframeWindow.functions.getQueryParams);
        window.functions.getQueryParams = getQueryParams;
        
        const setQueryParams = cloneFunction(iframeWindow.functions.setQueryParams);
        window.functions.setQueryParams = setQueryParams;

        const currIframeHref = iframeWindow.location.href.replace(baseUrl, "");
    
        if (currIframeHref !== iframeHref) {
            const query = getQueryParams();
            query.location = currIframeHref;
            setQueryParams(query);
        }

        iframeHref = currIframeHref;
    }, 0);
});

function cloneFunction(originalFunction) {
    let functionString = originalFunction.toString();

    functionString = functionString.replace(/^function\s*\w*\s*\([^)]*\)\s*{/, "");
    functionString = functionString.replace(/}$/, "");

    const paramsMatch = originalFunction.toString().match(/^function\s*\w*\s*\(([^)]*)\)/);
    const params = paramsMatch ? paramsMatch[1] : "";

    return new Function(params, functionString);
}
