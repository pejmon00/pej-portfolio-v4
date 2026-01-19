import { mountAppShell } from "./components/AppShell.js";
import { mountSecondaryNav } from "./components/SecondaryNav.js";

document.addEventListener("DOMContentLoaded", () => {
  mountAppShell(document.getElementById("app"));
  mountSecondaryNav(document.body);

  // Inject LongForm template where requested
  document.querySelectorAll("[data-longform]").forEach(async (el) => {
    const page = el.getAttribute("data-page");
    const tpl = await fetch("/src/templates/longform.html").then((r) =>
      r.text(),
    );
    el.innerHTML = tpl.replace("<!-- PAGE-ID -->", page || "");
    // Note: verbatim copy must be inserted by the client/developer into the DOM where indicated.
  });
});
