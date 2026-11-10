{{--
    Mobilni prikaz Filament tablica (ispod 768 px): svaki red postaje kartica,
    stupci dobiju oznaku iz zaglavlja, a gumbi akcija su uvijek vidljivi na dnu kartice.
    Desktop prikaz se ne mijenja. Uključeno u AdminPanelProvider (HEAD_END).
--}}
<style>
@media (max-width: 767px) {
    .fi-ta-content { overflow-x: visible; }
    .fi-ta-table, .fi-ta-table > tbody { display: block; width: 100%; }
    .fi-ta-table > thead { display: none; }

    .fi-ta-table tr.fi-ta-row {
        display: block;
        position: relative;
        padding: .5rem 0 0;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 0 !important;
        min-height: 0;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-label]::before {
        content: attr(data-label);
        flex-shrink: 0;
        max-width: 45%;
        padding-left: 1rem;
        font-size: .75rem;
        line-height: 1rem;
        color: rgb(107 114 128);
    }
    .dark .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-label]::before { color: rgb(156 163 175); }

    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell { white-space: normal; }

    /* vrijednost desno, manji razmak nego u tablici */
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell > .fi-ta-col-wrp {
        flex: 1 1 auto;
        min-width: 0;
        display: flex;
        justify-content: flex-end;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell > .fi-ta-col-wrp > a,
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell > .fi-ta-col-wrp > button {
        width: auto;
        min-width: 0;
        justify-content: flex-end;
    }
    .fi-ta-table tr.fi-ta-row .fi-ta-col-wrp :is(.fi-ta-text, .fi-ta-icon, .fi-ta-color, .fi-ta-image, .fi-ta-checkbox, .fi-ta-toggle) {
        width: auto;
        padding: .3rem 1rem .3rem 0 !important;
        justify-content: flex-end;
        justify-items: end;
        text-align: right;
    }
    .fi-ta-table tr.fi-ta-row .fi-ta-col-wrp :is(.fi-ta-select, .fi-ta-text-input) {
        min-width: 0;
        padding: .3rem 1rem .3rem 0;
    }
    .fi-ta-table tr.fi-ta-row .fi-ta-col-wrp .fi-ta-text > div { justify-content: flex-end; flex-wrap: wrap; }
    .fi-ta-table tr.fi-ta-row .fi-ta-text-item-label { overflow-wrap: anywhere; }

    /* prvi stupac = naslov kartice */
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-naslov] > .fi-ta-col-wrp,
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-naslov] > .fi-ta-col-wrp > :is(a, button) { justify-content: flex-start; }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-naslov] .fi-ta-col-wrp :is(.fi-ta-text, .fi-ta-icon, .fi-ta-image) {
        justify-content: flex-start;
        justify-items: start;
        text-align: left;
        padding: .25rem 3rem .25rem 1rem !important;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-naslov] .fi-ta-text > div { justify-content: flex-start; }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-cell[data-naslov] .fi-ta-text-item-label { font-weight: 600; font-size: 1rem; }

    /* kvačica za odabir gore desno */
    .fi-ta-table tr.fi-ta-row > td.fi-ta-selection-cell {
        position: absolute;
        top: .35rem;
        right: .25rem;
        display: block;
        width: auto;
    }

    /* gumbi: uvijek vidljivi, u traci na dnu kartice */
    .fi-ta-table tr.fi-ta-row > td.fi-ta-actions-cell {
        display: block;
        margin-top: .4rem;
        border-top: 1px solid rgb(229 231 235);
        background: rgb(249 250 251);
    }
    .dark .fi-ta-table tr.fi-ta-row > td.fi-ta-actions-cell {
        border-top-color: rgb(255 255 255 / .1);
        background: rgb(255 255 255 / .03);
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-actions-cell > div {
        white-space: normal;
        padding: .5rem 1rem;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-actions-cell .fi-ta-actions {
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: .25rem 1rem;
    }
    .fi-ta-table tr.fi-ta-row > td.fi-ta-actions-cell .fi-icon-btn {
        width: 2.5rem;
        height: 2.5rem;
    }
}
</style>
<script>
(() => {
    // Oznake stupaca iz zaglavlja u ćelije (data-label) i prvi stupac kao naslov kartice.
    const oznaci = (korijen) => {
        korijen.querySelectorAll('table.fi-ta-table').forEach((tablica) => {
            const oznake = {};
            tablica.querySelectorAll('thead th[class*="fi-table-header-cell-"]').forEach((th) => {
                const klasa = [...th.classList].find((c) => c.startsWith('fi-table-header-cell-'));
                const tekst = th.querySelector('.fi-ta-header-cell-label')?.textContent.trim();
                if (klasa && tekst) oznake[klasa.replace('fi-table-header-cell-', '')] = tekst;
            });
            tablica.querySelectorAll('tbody tr.fi-ta-row').forEach((red) => {
                let prvi = true;
                red.querySelectorAll(':scope > td[class*="fi-table-cell-"]').forEach((td) => {
                    const klasa = [...td.classList].find((c) => c.startsWith('fi-table-cell-'));
                    if (prvi) {
                        td.setAttribute('data-naslov', '');
                        td.removeAttribute('data-label');
                        prvi = false;
                        return;
                    }
                    const oznaka = klasa && oznake[klasa.replace('fi-table-cell-', '')];
                    if (oznaka && td.getAttribute('data-label') !== oznaka) td.setAttribute('data-label', oznaka);
                });
            });
        });
    };
    let zakazano = false;
    const zakazi = () => {
        if (zakazano) return;
        zakazano = true;
        requestAnimationFrame(() => { zakazano = false; oznaci(document); });
    };
    document.addEventListener('DOMContentLoaded', () => {
        oznaci(document);
        new MutationObserver(zakazi).observe(document.body, { childList: true, subtree: true });
    });
    document.addEventListener('livewire:navigated', zakazi);
})();
</script>
