(() => {
    window.__probeResult = {
        url: location.pathname,
        topnavLinks: [...document.querySelectorAll('header a, header button')].map((el) => el.textContent.trim()),
        sidebarLinks: [...document.querySelectorAll('aside a, aside button')].map((el) => el.textContent.trim()),
        overflow: document.documentElement.scrollWidth > document.documentElement.clientWidth,
    };
})();
