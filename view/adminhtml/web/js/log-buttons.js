define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'loader'
], function ($, modal) {
    'use strict';

    /**
     * Log lines are text, never markup: they arrive from the server as they were written.
     */
    function escapeHtml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[char]);
    }

    const modals = {};

    /**
     * Resolve the modal once: the widget moves the element out of the form, so a second scoped
     * lookup finds nothing.
     */
    function initModal(type, title) {
        const $modal = $(`form[action*="webshopnl"] #webshopnl-result_${type}-modal`);

        if (!$modal.length) {
            return;
        }
        modals[type] = $modal;

        modal({
            modalClass: 'webshopnl-modal',
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: title,
            buttons: [
                {
                    text: $.mage.__('download as .txt file'),
                    class: 'webshopnl-button__download',
                    click: () => {
                        const text = document.getElementById(`webshopnl-result_${type}`).innerText || '',
                            link = document.createElement('a');

                        link.setAttribute('download', `${type}-log.txt`);
                        link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
                        link.click();
                    }
                },
                {
                    text: $.mage.__('Close window'),
                    class: 'webshopnl-modal-close',
                    click: function () {
                        this.closeModal();
                    }
                }
            ]
        }, $modal);

        $modal.loader({texts: ''});
    }

    function render(response, $container) {
        if (Array.isArray(response.result)) {
            const items = response.result.map((entry) => `<li class="webshopnl-result-item">
                        <strong>${escapeHtml(entry.date)}</strong>
                        <p>${escapeHtml(entry.msg)}</p>
                    </li>`).join('');

            $container.find('.result').empty().append(`<ul>${items}</ul>`);
            return;
        }

        $container.find('.result').empty().append(`<p>${escapeHtml(response)}</p>`);
    }

    return function () {
        $(() => {
            initModal('debug', $.mage.__('Last 100 debug log records'));
            initModal('error', $.mage.__('Last 100 error log records'));
        });

        // Scoped to this module's own configuration form, so the handler does not fire for the
        // buttons another extension puts on the same page.
        $(document).on('click', 'form[action*="webshopnl"] [id^=webshopnl-button_]', function () {
            const type = this.id.split('_')[1];

            if (!modals[type]) {
                // A button without a modal of its own, such as the help link: it does its own thing.
                return;
            }

            const $modal = modals[type],
                $result = $modal.find(`#webshopnl-result_${type}`);

            $modal.modal('openModal').loader('show');
            $result.hide();

            fetch($modal.attr('data-webshopnl-endpoint-url'))
                .then((res) => res.clone().json().catch(() => res.text()))
                .then((data) => render(data, $result))
                .catch(() => {
                    $result.find('.result').empty().append(`<p>${escapeHtml($.mage.__(
                        'Failed to load the log. Disable the ad blocker and try again.'
                    ))}</p>`);
                })
                .finally(() => {
                    // In finally rather than then(): a handler that throws would otherwise leave
                    // the spinner on screen.
                    $result.fadeIn();
                    $modal.loader('hide');
                });
        });
    };
});
