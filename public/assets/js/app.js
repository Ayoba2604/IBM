/* SIG-Banca · interface interactions */
(() => {
    "use strict";

    /* ------------------------------------------------------------------ */
    /* Mobile sidebar                                                     */
    /* ------------------------------------------------------------------ */
    const sidebar = document.getElementById("sidebar");
    const toggle = document.querySelector("[data-sidebar-toggle]");
    const scrim = document.querySelector("[data-sidebar-close]");
    let lastFocused = null;

    const setSidebar = (open) => {
        if (!sidebar || !toggle) return;
        sidebar.classList.toggle("open", open);
        scrim?.classList.toggle("visible", open);
        document.body.classList.toggle("nav-open", open);
        toggle.setAttribute("aria-expanded", String(open));
        if (open) {
            lastFocused = document.activeElement;
            sidebar.querySelector("a, button")?.focus();
        } else if (lastFocused instanceof HTMLElement) {
            lastFocused.focus();
        }
    };

    toggle?.addEventListener("click", () => setSidebar(!sidebar.classList.contains("open")));
    scrim?.addEventListener("click", () => setSidebar(false));
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && sidebar?.classList.contains("open")) {
            setSidebar(false);
        }
    });
    window.addEventListener("resize", () => {
        if (window.innerWidth > 1024) setSidebar(false);
    });

    /* ------------------------------------------------------------------ */
    /* Notification popover and inline menus                              */
    /* ------------------------------------------------------------------ */
    const closeDetails = (except) => {
        document.querySelectorAll("details[open]").forEach((details) => {
            if (details !== except) details.removeAttribute("open");
        });
    };

    document.addEventListener("click", (event) => {
        const details = event.target.closest("details");
        if (!details) {
            closeDetails();
            return;
        }
        if (!details.contains(event.target)) closeDetails();
    });

    document.querySelectorAll("details").forEach((details) => {
        details.addEventListener("toggle", () => {
            if (details.open) closeDetails(details);
        });
    });

    /* ------------------------------------------------------------------ */
    /* Alerts                                                             */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll("[data-dismiss]").forEach((button) => {
        button.addEventListener("click", () => {
            const alert = button.closest(".alert");
            if (!alert) return;
            alert.style.transition = "opacity .2s ease, transform .2s ease";
            alert.style.opacity = "0";
            alert.style.transform = "translateY(-4px)";
            window.setTimeout(() => alert.remove(), 200);
        });
    });

    document.querySelectorAll(".alert.success, .alert.info").forEach((alert) => {
        let timeout = window.setTimeout(() => {
            alert.style.opacity = "0";
            window.setTimeout(() => alert.remove(), 220);
        }, 9000);
        alert.addEventListener("mouseenter", () => window.clearTimeout(timeout));
        alert.addEventListener("mouseleave", () => {
            timeout = window.setTimeout(() => alert.remove(), 3000);
        });
    });

    /* ------------------------------------------------------------------ */
    /* Confirmation prompts                                               */
    /* ------------------------------------------------------------------ */
    document.addEventListener("submit", (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        const submitter = event.submitter;
        const message = submitter?.dataset.confirm || form.dataset.confirm;
        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }
        const label = submitter?.dataset.loading;
        if (label) {
            window.setTimeout(() => {
                submitter.textContent = label;
                submitter.disabled = true;
            }, 0);
        }
    });

    /* ------------------------------------------------------------------ */
    /* Password visibility                                                */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll("[data-password-toggle]").forEach((button) => {
        button.addEventListener("click", () => {
            const input = button.parentElement?.querySelector("input");
            if (!input) return;
            const show = input.type === "password";
            input.type = show ? "text" : "password";
            button.setAttribute("aria-label", show ? "Ocultar senha" : "Mostrar senha");
            const icon = button.querySelector("i");
            icon?.classList.toggle("bi-eye", !show);
            icon?.classList.toggle("bi-eye-slash", show);
        });
    });

    /* ------------------------------------------------------------------ */
    /* In-page list filters (students / evaluators)                       */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll("[data-check-filter]").forEach((input) => {
        const target = document.querySelector(input.dataset.checkFilter);
        if (!target) return;
        const items = () => Array.from(target.querySelectorAll(".check"));
        input.addEventListener("input", () => {
            const term = input.value.trim().toLocaleLowerCase("pt-BR");
            items().forEach((item) => {
                item.hidden = term !== "" && !item.textContent.toLocaleLowerCase("pt-BR").includes(term);
            });
        });
    });

    /* ------------------------------------------------------------------ */
    /* Modality-dependent fields                                          */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll("[data-modality]").forEach((select) => {
        const form = select.closest("form");
        const room = form?.querySelector("[data-room]");
        const link = form?.querySelector("[data-link]");
        const sync = () => {
            const mode = select.value;
            const needsRoom = mode === "presencial" || mode === "hibrida";
            const needsLink = mode === "online" || mode === "hibrida";
            if (room) {
                room.closest(".field")?.classList.toggle("field-hidden", !needsRoom);
                room.required = needsRoom;
            }
            if (link) {
                link.closest(".field")?.classList.toggle("field-hidden", !needsLink);
                link.required = needsLink;
            }
        };
        select.addEventListener("change", sync);
        sync();
    });

    /* ------------------------------------------------------------------ */
    /* Lightweight table sorting                                          */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll("table[data-sortable] th[data-sort]").forEach((header, index) => {
        header.style.cursor = "pointer";
        header.addEventListener("click", () => {
            const table = header.closest("table");
            const body = table?.querySelector("tbody");
            if (!body) return;
            const rows = Array.from(body.querySelectorAll("tr"));
            const direction = header.dataset.direction === "asc" ? "desc" : "asc";
            header.dataset.direction = direction;
            const column = Number(header.dataset.sort ?? index);
            const value = (row) => row.children[column]?.textContent.trim().toLocaleLowerCase("pt-BR") ?? "";
            rows.sort((a, b) => value(a).localeCompare(value(b), "pt-BR", { numeric: true }) * (direction === "asc" ? 1 : -1));
            rows.forEach((row) => body.appendChild(row));
        });
    });
})();
