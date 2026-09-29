import { Controller } from '@hotwired/stimulus';

/*
 * Opens a <dialog> (driven by the `modal` controller) from anywhere in the page.
 * The trigger passes Stimulus action params; every param except `id` is forwarded
 * to the dialog in the `modal:open` event detail (see modal_controller.js).
 *
 * <twig:Button
 *     data-controller="modal-trigger"
 *     data-action="modal-trigger#open"
 *     data-modal-trigger-id-param="confirm-delete-client"
 *     data-modal-trigger-action-param="{{ path('client_delete', {id: client.id}) }}"
 *     data-modal-trigger-subject-param="{{ client.name }}"
 * >…</twig:Button>
 */
export default class extends Controller {
    open(event) {
        event.preventDefault();
        const { id, ...detail } = event.params;
        const dialog = document.getElementById(id);

        if (!dialog) {
            console.warn(`[modal-trigger] no dialog with id "${id}"`);
            return;
        }

        dialog.dispatchEvent(new CustomEvent('modal:open', { detail }));
    }
}
