document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.job-list__delete').forEach(function(link) {
        link.addEventListener('click', function(e) {
            const confirmed = confirm('Diesen Job wirklich löschen?');
            if (!confirmed) {
                e.preventDefault(); // ❌ Abbrechen
            }
        });
    });
});
