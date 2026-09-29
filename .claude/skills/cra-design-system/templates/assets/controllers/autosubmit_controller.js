import { Controller } from '@hotwired/stimulus';

/*
 * Submits its <form> as soon as a control changes (Turbo handles the navigation).
 * Sets data-autosubmit-ready so CSS can hide the no-JS submit button.
 * Markup: <form data-controller="autosubmit"> … <select data-action="autosubmit#submit">
 */
export default class extends Controller {
    connect() {
        this.element.dataset.autosubmitReady = '';
    }

    submit() {
        this.element.requestSubmit();
    }
}
