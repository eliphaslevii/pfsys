/**
 * ======================================================
 *  🔧 FUNÇÕES DE UTILIDADE DO MÓDULO
 * ======================================================
 */

export async function apiFetch(url, method = "GET", body = null) {
    const headers = {
        "Accept": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    };

    if (method !== "GET") {
        headers["Content-Type"] = "application/json";
    }

    const response = await fetch(url, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
    });

    return response.json();
}


export function formatMoney(value) {
    if (!value) return "0,00";

    return parseFloat(value)
        .toFixed(2)
        .replace(".", ",");
}

export const notyf = new Notyf({
    duration: 2500,
    position: { x: "right", y: "top" }
});
