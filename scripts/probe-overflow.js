(() => {
    const dok = document.documentElement;
    const luapan = [...document.querySelectorAll('body *')]
        .filter((el) => el.getBoundingClientRect().right > dok.clientWidth + 1)
        .map((el) => `${el.tagName}.${el.className}`.slice(0, 60));

    window.__probeResult = {
        url: location.pathname,
        clientWidth: dok.clientWidth,
        scrollWidth: dok.scrollWidth,
        luapan: luapan.slice(0, 5),
        jumlahH1: [...document.querySelectorAll('h1')].filter((h) => h.offsetParent !== null).length,
        cloakTersisa: document.querySelectorAll('[x-cloak]').length,
    };
})();
