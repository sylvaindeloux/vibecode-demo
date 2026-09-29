import { Controller } from '@hotwired/stimulus';

/*
 * "Imprimer / Enregistrer en PDF": opens the browser print dialog.
 * The PDF file name defaults to the page <title> (see references/print.md).
 * Markup: <twig:PrintButton />
 */
export default class extends Controller {
    print(event) {
        event.preventDefault();
        window.print();
    }
}
