import { Controller } from '@hotwired/stimulus';

/*
 * Tooltip dismissal (WCAG 1.4.13): Escape hides the bubble without moving focus;
 * it shows again on the next hover/focus.
 * Markup: <twig:Tooltip> (CSS handles show on :hover / :focus-within).
 */
export default class extends Controller {
    dismiss(event) {
        if (event.key === 'Escape') {
            this.element.dataset.tooltipDismissed = '';
        }
    }

    reset() {
        delete this.element.dataset.tooltipDismissed;
    }
}
