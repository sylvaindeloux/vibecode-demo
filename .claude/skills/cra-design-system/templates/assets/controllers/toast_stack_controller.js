import { Controller } from '@hotwired/stimulus';

/*
 * Toast region. Server toasts come from Symfony flash messages (rendered by <twig:ToastStack>).
 * Client code shows a toast by dispatching a window event:
 *
 *   window.dispatchEvent(new CustomEvent('toast:show', {
 *       detail: { tone: 'error', title: 'Enregistrement impossible', message: 'Réessayez.' },
 *   }));
 *
 * Each tone has a <template data-toast-stack-target="template" data-tone="…"> rendered by Twig
 * (icon + close label), so no markup or text is hard-coded here.
 */
export default class extends Controller {
    static targets = ['template'];

    show({ detail }) {
        const tone = detail.tone ?? 'info';
        const template = this.templateTargets.find((t) => t.dataset.tone === tone);
        if (!template) return;

        const toast = template.content.firstElementChild.cloneNode(true);
        toast.querySelector('[data-toast-slot="title"]').textContent = detail.title ?? '';
        const message = toast.querySelector('[data-toast-slot="message"]');
        if (detail.message) {
            message.textContent = detail.message;
        } else {
            message.remove();
        }

        this.element.append(toast);
    }
}
