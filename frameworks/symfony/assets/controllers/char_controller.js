import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        /**
         * Add a special char on click to current input question.
         */
        this.element.addEventListener('click', () => {
            const char = this.element.innerText;
            // TODO: handle multiple enabled inputs (questions).
            const inputs = document.querySelectorAll('input:enabled');
            if (inputs.length === 0) {
                return;
            }

            const value = inputs[0].value;
            inputs.forEach((input) => {
                input.value = value + char;
            });
            inputs[0].focus();
        });
    }
}
