import { Controller } from '@hotwired/stimulus';

/*
 * One toast. Auto-dismisses after `duration` ms unless `persistent` (warning/error toasts),
 * pauses while hovered or focused, closes with its close button.
 * Markup: <twig:Toast> (data-controller="toast").
 */
export default class extends Controller {
    static values = {
        duration: { type: Number, default: 5000 }, // mirrors --duration-toast
        persistent: Boolean,
    };

    connect() {
        this.start();
    }

    disconnect() {
        clearTimeout(this.timer);
    }

    start() {
        if (this.persistentValue) return;
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.close(), this.durationValue);
    }

    pause() {
        clearTimeout(this.timer);
    }

    close() {
        clearTimeout(this.timer);
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion) {
            this.element.remove();
            return;
        }
        this.element.dataset.leaving = '';
        this.element.addEventListener('transitionend', () => this.element.remove(), { once: true });
    }
}
