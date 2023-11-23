function SDHshowToast (title, text, delay = 3000) {
    const toastID = 'toast-' + Date.now(); // Генерируем уникальный идентификатор для каждого тоста

    const toastHtml = `
        <div class="position-fixed bottom-0 start-0 p-3" style="z-index: 11">
            <div id="${toastID}" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="${delay}">
                <div class="toast-header">
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Закрыть"></button>
                </div>
                <div class="toast-body">
                    ${text}
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', toastHtml);

    const toastElement = document.getElementById(toastID);
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}
