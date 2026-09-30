import { Controller } from '@hotwired/stimulus';

/*
 * Modal on a native <dialog>.
 * - open(): showModal() (top layer, focus trap, Escape to close are native).
 *   When opened through the `modal:open` event, `event.detail` fills the dialog:
 *     detail.action -> action attribute of the target form
 *     detail.token  -> value of the form's hidden _token input
 *     any other key -> textContent of [data-modal-slot="<key>"] elements
 * - close(): dialog.close(); focus returns to the previously focused element natively.
 * - Clicking the backdrop closes the dialog.
 * - Closed before Turbo caches the page, so a restored page never shows a stale dialog.
 *
 * Markup: <twig:Modal> / <twig:ConfirmDialog> (they wire the actions below).
 *   data-controller="modal"
 *   data-action="modal:open->modal#open click->modal#backdropClose turbo:before-cache@document->modal#close"
 */
export default class extends Controller {
    static targets = ['form', 'initialFocus'];
    static values = { openOnConnect: Boolean };

    connect() {
        if (this.openOnConnectValue) {
            this.open();
        }
    }

    disconnect() {
        this.close();
    }

    open(event) {
        const detail = event?.detail ?? {};

        Object.entries(detail).forEach(([key, value]) => {
            if (key === 'action' && this.hasFormTarget) {
                this.formTarget.action = value;
            } else if (key === 'token' && this.hasFormTarget) {
                const input = this.formTarget.querySelector('input[name="_token"]');
                if (input) input.value = value;
            } else {
                this.element.querySelectorAll(`[data-modal-slot="${key}"]`).forEach((node) => {
                    node.textContent = value;
                });
            }
        });

        if (!this.element.open) {
            this.element.showModal();
        }

        if (this.hasInitialFocusTarget) {
            this.initialFocusTarget.focus();
        }
    }

    close() {
        if (this.element.open) {
            this.element.close();
        }
    }

    backdropClose(event) {
        // A click whose target is the <dialog> itself landed on the backdrop.
        if (event.target === this.element) {
            this.close();
        }
    }
}
