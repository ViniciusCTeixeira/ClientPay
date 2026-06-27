document.addEventListener('submit', function (event) {
    const message = event.target.getAttribute('data-confirm');
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

const invoiceClient = document.getElementById('invoice-client');
const invoiceSite = document.getElementById('invoice-site');
if (invoiceClient && invoiceSite) {
    const filterSites = function (resetSelection) {
        const clientId = invoiceClient.value;
        Array.from(invoiceSite.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden = Boolean(clientId) && option.dataset.client !== clientId;
            option.disabled = option.hidden;
        });
        if (resetSelection && invoiceSite.selectedOptions[0]?.disabled) {
            invoiceSite.value = '';
        }
    };
    invoiceClient.addEventListener('change', function () { filterSites(true); });
    filterSites(false);
}
