/**
 * FaktaForm Interactivity Script (Version 5.0 - Endelig Løsning med Korrekt Element-Måling)
 * Løser måle-problemet ved at sigte efter den indre container.
 */
document.addEventListener('DOMContentLoaded', function () {

    function initFaktaAccordion(accordionElement) {
        const panel = accordionElement.querySelector('.fakta-accordion__panel');
        const toggleButton = accordionElement.querySelector('.fakta-accordion__toggle');

        if (!panel || !toggleButton) return;

        const collapsedHeight = accordionElement.dataset.collapsedHeight || '200px';
        let hasBeenChecked = false;

        const checkOverflow = () => {
            if (hasBeenChecked) return;

            // DEN AFGØRENDE ÆNDRING: Find en mere pålidelig indre container at måle på.
            const innerContainer = panel.querySelector('.gb-inside-container, .fakta-accordion__panel-inner');
            const elementToMeasure = innerContainer || panel; // Brug den indre, hvis den findes, ellers fald tilbage til panelet selv.

            if (elementToMeasure.scrollHeight <= 1) return;
            
            panel.style.maxHeight = collapsedHeight;

            if (elementToMeasure.scrollHeight > parseInt(collapsedHeight) + 1) {
                accordionElement.classList.add('has-overflow');
                accordionElement.classList.remove('no-overflow');
            } else {
                accordionElement.classList.remove('has-overflow');
                accordionElement.classList.add('no-overflow');
            }

            hasBeenChecked = true;
            resizeObserver.disconnect();
        };

        const resizeObserver = new ResizeObserver(checkOverflow);
        resizeObserver.observe(panel);

        toggleButton.addEventListener('click', function () {
            accordionElement.classList.toggle('is-open');
            toggleButton.setAttribute('aria-expanded', accordionElement.classList.contains('is-open'));
            if (accordionElement.classList.contains('is-open')) {
                panel.style.maxHeight = panel.scrollHeight + 'px';
            } else {
                panel.style.maxHeight = collapsedHeight;
            }
        });
    }

    const accordions = document.querySelectorAll('.fakta-accordion');

    accordions.forEach(function (accordion) {
        if (accordion.querySelector('.fakta-accordion__panel') && accordion.querySelector('.fakta-accordion__toggle')) {
            initFaktaAccordion(accordion);
        } else {
            const observer = new MutationObserver(function (mutations, obs) {
                if (accordion.querySelector('.fakta-accordion__panel') && accordion.querySelector('.fakta-accordion__toggle')) {
                    initFaktaAccordion(accordion);
                    obs.disconnect();
                }
            });
            observer.observe(accordion, { childList: true });
        }
    });
});