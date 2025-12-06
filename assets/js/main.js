function updateSlider(slider) {
    const output = slider.nextElementSibling.nextElementSibling;
    output.textContent = slider.value;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('questionnaireForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            alert('✅ Respons berhasil dikirim!\n\n(Dalam implementasi penuh, data akan disimpan ke database.)');
        });
    }
});