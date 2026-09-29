import { Controller } from '@hotwired/stimulus';

/*
 * Theme switch: light | dark | system.
 * - Sets/removes <html data-theme> immediately.
 * - Persists the choice in the `theme` cookie so base.html.twig renders the right
 *   data-theme server-side on the next request (no flash, no inline script).
 *
 * Markup: <twig:ThemeSwitch /> (radio group, data-action="change->theme#change").
 */
export default class extends Controller {
    static values = {
        cookieName: { type: String, default: 'theme' },
        maxAge: { type: Number, default: 31536000 }, // one year, in seconds
    };

    change(event) {
        const choice = event.target.value;
        const root = document.documentElement;

        if (choice === 'light' || choice === 'dark') {
            root.dataset.theme = choice;
        } else {
            delete root.dataset.theme;
        }

        document.cookie = `${this.cookieNameValue}=${encodeURIComponent(choice)}; path=/; max-age=${this.maxAgeValue}; SameSite=Lax`;
    }
}
