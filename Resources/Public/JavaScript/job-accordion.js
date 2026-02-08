document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.job-accordion .job-item');

    items.forEach(item => {
        const header = item.querySelector('.job-header');

        header.addEventListener('click', () => {
            const isOpen = item.classList.contains('active');

            // Alle schließen (klassisches Accordion)
            items.forEach(i => i.classList.remove('active'));

            // Falls vorher geschlossen → öffnen
            if (!isOpen) {
                item.classList.add('active');
            }
        });
    });
});
