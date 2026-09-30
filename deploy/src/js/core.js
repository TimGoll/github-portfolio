// all pages are prerendered by the cache rebuild and work without JavaScript, this script only
// adds the transitions between the landing page and the project pages without reloading the page

const landing = document.getElementById("landing");
const popup = document.getElementById("popup");
const landingTitle = document.body.dataset.title;

// the popup content of every project page that was already loaded
const pages = new Map();

var lastScrollPos = 0;

function isPopupOpen() {
    return popup.style.display !== "none";
}

async function requestPage(projectId) {
    if (!pages.has(projectId)) {
        const response = await fetch("/" + projectId, {
            credentials: "omit"
        });

        const page = new DOMParser().parseFromString(await response.text(), "text/html");

        // unknown projects are answered with the landing page
        if (!response.ok || page.body.dataset.page !== projectId) {
            return undefined;
        }

        pages.set(projectId, {
            title: page.title,
            popup: page.getElementById("popup").innerHTML
        });
    }

    return pages.get(projectId);
}

async function openProject(projectId, preventStatePush) {
    let page;

    try {
        page = await requestPage(projectId);
    } catch (error) {
        page = undefined;
    }

    // if the page can't be loaded in the background, the browser should simply open it
    if (page === undefined) {
        window.location.href = "/" + projectId;

        return;
    }

    // cache the last scroll position and reset the scroll pos to 0
    if (!isPopupOpen()) {
        lastScrollPos = window.scrollY;
    }

    popup.innerHTML = page.popup;

    // hide landing page and unhide popup
    landing.setAttribute("style", "display: none;");
    popup.setAttribute("style", "display: block; min-height: 100%;");
    window.scroll(0, 0);

    // update browser history
    if (!preventStatePush) {
        window.history.pushState({}, "", "/" + projectId);
    }
    window.document.title = page.title;
}

function closeProject(preventStatePush) {
    if (!isPopupOpen()) {
        return;
    }

    landing.setAttribute("style", "display: block;");
    popup.setAttribute("style", "display: none;");
    window.scroll(0, lastScrollPos);

    if (!preventStatePush) {
        window.history.pushState({}, "", "/");
    }
    window.document.title = landingTitle;
}

function setPage(projectId, preventStatePush) {
    if (projectId != "") {
        openProject(projectId, preventStatePush);
    } else {
        closeProject(preventStatePush);
    }
}

function currentProjectId() {
    return window.location.pathname.replace(/^\/|\/$/g, "");
}

// links to projects and the close button are handled here, the listener is on the document
// because the content of the popup is replaced
document.addEventListener("click", function(e) {
    // let the browser handle new tabs and windows
    if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) {
        return;
    }

    const projectLink = e.target.closest("a[data-project]");

    if (projectLink) {
        e.preventDefault();
        openProject(projectLink.dataset.project);

        return;
    }

    if (e.target.closest("#button-close")) {
        e.preventDefault();
        closeProject();
    }
});

window.addEventListener('keyup', function(e) {
    if (e.defaultPrevented) {
        return;
    }

    var key = e.key || e.keyCode;

    if (key === 'Escape' || key === 'Esc' || key === 27) {
        closeProject();
    }
});

//catch history change events
window.onpopstate = function() {
    const closing = currentProjectId() == "";

    setPage(currentProjectId(), true);

    // hacky solution to guarantee that the scrolling is reset
    if (closing) {
        window.setTimeout(function() {
            window.scroll(0, lastScrollPos);
        }, 0);
    }
};

// unknown pages are answered with the landing page, the url should reflect that
if (!isPopupOpen() && currentProjectId() != "") {
    window.history.replaceState({}, "", "/");
}
