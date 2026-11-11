import './bootstrap';

import Alpine from 'alpinejs';
import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;

Alpine.start();

const orderChartCanvas = document.querySelector('[data-dashboard-orders]');

if (orderChartCanvas) {
    const revenueValues = JSON.parse(orderChartCanvas.dataset.revenue);
    const maximumRevenue = Math.max(...revenueValues, 0);
    const revenueStep = Math.max(50000, Math.ceil(maximumRevenue / 5 / 50000) * 50000);
    const revenueMaximum = Math.max(250000, Math.ceil(maximumRevenue / revenueStep) * revenueStep);

    new Chart(orderChartCanvas, {
        type: 'line',
        data: {
            labels: JSON.parse(orderChartCanvas.dataset.labels),
            datasets: [
                {
                    label: 'Pesanan',
                    data: JSON.parse(orderChartCanvas.dataset.orders),
                    borderColor: '#5146e5',
                    backgroundColor: 'rgba(81, 70, 229, .12)',
                    yAxisID: 'orders',
                    tension: 0.35,
                    fill: true,
                },
                {
                    label: 'Pendapatan',
                    data: revenueValues,
                    borderColor: '#e45d88',
                    backgroundColor: 'rgba(228, 93, 136, .12)',
                    yAxisID: 'revenue',
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', align: 'center', labels: { boxWidth: 18, padding: 12 } },
            },
            scales: {
                orders: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: { precision: 0, font: { size: 10 } },
                    title: { display: true, text: 'Pesanan' },
                },
                revenue: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    max: revenueMaximum,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        stepSize: revenueStep,
                        precision: 0,
                        font: { size: 10 },
                        callback: (value) => `Rp ${new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(value)}`,
                    },
                    title: { display: true, text: 'Pendapatan' },
                },
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 7,
                        maxRotation: 0,
                        minRotation: 0,
                        font: { size: 10 },
                    },
                },
            },
            onResize(chart, size) {
                const compact = size.width < 520;
                chart.options.scales.orders.ticks.display = !compact;
                chart.options.scales.orders.title.display = !compact;
                chart.options.scales.revenue.title.display = !compact;
                chart.options.scales.revenue.ticks.maxTicksLimit = compact ? 4 : undefined;
                chart.options.scales.revenue.ticks.font = { size: compact ? 9 : 10 };
                chart.options.scales.x.ticks.maxTicksLimit = compact ? 4 : 7;
                chart.options.scales.x.ticks.font = { size: compact ? 9 : 10 };
                chart.options.plugins.legend.position = compact ? 'bottom' : 'top';
                chart.update('none');
            },
        },
    });
}

const clickChartCanvas = document.querySelector('[data-dashboard-clicks]');

if (clickChartCanvas) {
    new Chart(clickChartCanvas, {
        type: 'doughnut',
        data: {
            labels: JSON.parse(clickChartCanvas.dataset.labels),
            datasets: [{
                data: JSON.parse(clickChartCanvas.dataset.values),
                backgroundColor: ['#facc15', '#2563eb', '#059669', '#dc2626', '#7c3aed', '#db2777'],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 8,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 18, padding: 12 } } },
        },
    });
}

const orderStatusCanvas = document.querySelector('[data-dashboard-order-status]');

if (orderStatusCanvas) {
    new Chart(orderStatusCanvas, {
        type: 'doughnut',
        data: {
            labels: JSON.parse(orderStatusCanvas.dataset.labels),
            datasets: [{
                data: JSON.parse(orderStatusCanvas.dataset.values),
                backgroundColor: ['#facc15', '#2563eb', '#059669', '#dc2626'],
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 8,
            }],
        },
        options: {
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 18, padding: 12 } } },
        },
    });
}

function initializeProductImageForms(container = document) {
    container.querySelectorAll('[data-product-image-form]:not([data-cropper-ready])').forEach((form) => {
        form.dataset.cropperReady = 'true';
        const sourceInput = form.querySelector('[data-image-source]');
        const croppedInput = form.querySelector('[data-cropped-image]');
        const cropperContainer = form.querySelector('[data-cropper-container]');
        const cropperImage = form.querySelector('[data-cropper-image]');
        const cropButton = form.querySelector('[data-crop-image]');
        const previewContainer = form.querySelector('[data-cropped-preview-container]');
        const croppedPreview = form.querySelector('[data-cropped-preview]');
        const status = form.querySelector('[data-crop-status]');
        let cropper;
        let sourceUrl;
        let previewUrl;

        sourceInput.addEventListener('change', () => {
            cropper?.destroy();
            cropper = undefined;
            croppedInput.value = '';
            previewContainer.classList.add('hidden');
            status.textContent = '';

            if (sourceUrl) {
                URL.revokeObjectURL(sourceUrl);
            }

            const [file] = sourceInput.files;

            if (!file) {
                cropperContainer.classList.add('hidden');
                cropButton.classList.add('hidden');
                return;
            }

            sourceUrl = URL.createObjectURL(file);
            cropperImage.src = sourceUrl;
            cropperContainer.classList.remove('hidden');
            cropButton.classList.remove('hidden');
            cropper = new Cropper(cropperImage, {
                aspectRatio: 1,
                autoCropArea: 1,
                viewMode: 1,
            });
        });

        cropButton.addEventListener('click', () => {
            if (!cropper) {
                return;
            }

            cropButton.disabled = true;
            status.textContent = 'Memproses gambar...';
            cropper.getCroppedCanvas({
                width: 800,
                height: 800,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            }).toBlob((blob) => {
                cropButton.disabled = false;

                if (!blob) {
                    status.textContent = 'Gambar gagal diproses. Silakan pilih gambar lain.';
                    return;
                }

                if (blob.size > 2 * 1024 * 1024) {
                    status.textContent = 'Ukuran hasil potongan melebihi 2 MB. Perkecil gambar atau pilih gambar lain.';
                    return;
                }

                const extension = blob.type === 'image/png' ? 'png' : blob.type === 'image/webp' ? 'webp' : 'jpg';
                const croppedFile = new File([blob], `product-image.${extension}`, { type: blob.type });
                const transfer = new DataTransfer();
                transfer.items.add(croppedFile);
                croppedInput.files = transfer.files;

                if (previewUrl) {
                    URL.revokeObjectURL(previewUrl);
                }

                previewUrl = URL.createObjectURL(blob);
                croppedPreview.src = previewUrl;
                previewContainer.classList.remove('hidden');
                status.textContent = '';
            }, sourceInput.files[0]?.type || 'image/jpeg', 0.9);
        });

        form.addEventListener('submit', (event) => {
            if (sourceInput.files.length > 0 && croppedInput.files.length === 0) {
                event.preventDefault();
                status.textContent = 'Klik "Gunakan hasil potongan" sebelum menyimpan gambar.';
            } else if (form.dataset.imageRequired === 'true' && croppedInput.files.length === 0) {
                event.preventDefault();
                status.textContent = 'Pilih dan potong gambar produk sebelum menyimpan.';
            }
        });
    });
}

initializeProductImageForms();

document.querySelectorAll('[data-admin-modal]').forEach((dialog) => {
    const content = dialog.querySelector('[data-admin-modal-content]');
    let returnContent = null;
    let returnSelect = null;
    let opener = null;
    let successAction = null;

    function restoreProductForm(category = null) {
        if (returnContent) {
            content.replaceChildren(returnContent);
            returnContent = null;
        }

        if (category && returnSelect) {
            returnSelect.add(new Option(category.name, category.id, true, true));
            returnSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }

        returnSelect?.focus();
        returnSelect = null;
        successAction = null;
        initializeProductImageForms(content);
    }

    function dismissModal() {
        if (returnContent) {
            restoreProductForm();
            return;
        }

        dialog.close();
    }

    document.addEventListener('click', async (event) => {
        const trigger = event.target.closest('[data-admin-modal-trigger]');

        if (trigger) {
            event.preventDefault();
            opener = trigger;
            successAction = trigger.dataset.modalSuccess || null;

            if (successAction === 'category-option') {
                returnSelect = trigger.closest('[data-product-image-form]')?.querySelector('[data-product-category-select]')
                    || document.querySelector('[data-product-category-select]');

                if (dialog.open && returnSelect) {
                    returnContent = document.createDocumentFragment();
                    while (content.firstChild) {
                        returnContent.append(content.firstChild);
                    }
                }
            }

            if (!dialog.open) {
                dialog.showModal();
            }
            content.innerHTML = '<p class="p-6 text-sm text-gray-600">Memuat formulir...</p>';

            try {
                const response = await fetch(trigger.dataset.modalUrl || trigger.href, {
                    headers: { 'X-Admin-Modal': 'true', 'Accept': 'application/json' },
                });
                const result = await response.json();

                if (!response.ok || !result.html) {
                    throw new Error(result.message || 'Formulir tidak dapat dimuat.');
                }

                content.innerHTML = result.html;
                initializeProductImageForms(content);
                content.querySelector('[autofocus]')?.focus();
            } catch (error) {
                content.innerHTML = `<div class="p-6 text-sm text-red-700">${error.message}</div><div class="px-6 pb-6 text-right"><button type="button" class="rounded-md border px-4 py-2" data-admin-modal-close>Tutup</button></div>`;
            }

            return;
        }

        if (event.target.closest('[data-admin-modal-close]')) {
            const owner = event.target.closest('[data-admin-modal]');
            if (owner) {
                dismissModal();
            }
        }
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dismissModal();
        }
    });

    dialog.addEventListener('cancel', (event) => {
        if (returnContent) {
            event.preventDefault();
            restoreProductForm();
        }
    });

    dialog.addEventListener('close', () => {
        content.innerHTML = '';
        opener?.focus();
    });

    dialog.addEventListener('submit', async (event) => {
        const form = event.target.closest('[data-admin-modal-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        const submitButton = form.querySelector('[data-modal-submit]');
        const formErrors = form.querySelector('[data-form-errors]');
        form.querySelectorAll('[data-field-error]').forEach((item) => { item.textContent = ''; });
        formErrors.classList.add('hidden');
        submitButton.disabled = true;
        submitButton.textContent = 'Menyimpan...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();

            if (response.status === 422) {
                Object.entries(result.errors || {}).forEach(([field, messages]) => {
                    const error = form.querySelector(`[data-field-error="${field}"]`);
                    if (error) {
                        error.textContent = messages[0];
                    }
                });
                formErrors.textContent = 'Periksa kembali data yang dimasukkan.';
                formErrors.classList.remove('hidden');
                return;
            }

            if (!response.ok) {
                throw new Error(result.message || 'Data gagal disimpan. Silakan coba lagi.');
            }

            if (successAction === 'category-option' && result.category) {
                if (returnSelect) {
                    if (returnContent) {
                        restoreProductForm(result.category);
                    } else {
                        dialog.close();
                        returnSelect.add(new Option(result.category.name, result.category.id, true, true));
                        returnSelect.dispatchEvent(new Event('change', { bubbles: true }));
                        returnSelect.focus();
                        returnSelect = null;
                        successAction = null;
                    }
                }
                return;
            }

            dialog.close();
            window.location.reload();
        } catch (error) {
            formErrors.textContent = error.message;
            formErrors.classList.remove('hidden');
        } finally {
            if (submitButton.isConnected) {
                submitButton.disabled = false;
                submitButton.textContent = submitButton.form.querySelector('[name="_method"]') ? 'Simpan Perubahan' : 'Tambah Kategori';
            }
        }
    });
});
