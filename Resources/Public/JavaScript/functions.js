document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('deleteModal');
    const confirmBtn = modal.querySelector('.js-confirm-delete');

    document.querySelectorAll('.js-open-delete-modal').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            confirmBtn.href = this.dataset.url;
            modal.classList.add('active');
        });
    });

    modal.querySelector('.js-cancel').addEventListener('click', function () {
        modal.classList.remove('active');
    });

    modal.querySelector('.custom-modal__overlay').addEventListener('click', function () {
        modal.classList.remove('active');
    });
});
