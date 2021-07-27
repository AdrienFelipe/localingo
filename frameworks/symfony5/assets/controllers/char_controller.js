import { Controller } from 'stimulus';

export default class extends Controller {
    connect() {
        /**
         * Add a special char on click to current input question.
         */
        this.element.onclick = function() {
            const char = this.innerText;
            // TODO: handle multiple enabled inputs (questions).
            let $inputSelector = $("input:enabled");
            let value = $inputSelector.val();
            $inputSelector.val(value + char);
            $inputSelector.focus();
        };
    }
}
