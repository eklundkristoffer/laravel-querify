import { pick } from '../helpers';

export default class Column {
    constructor(columnComponent) {
        const properties = pick(columnComponent, [
            'label', 'show', 'formatter', 'sortable', 'filterable',
        ]);

        for (const property in properties) {
            this[property] = columnComponent[property];
        }

        this.template = columnComponent.$scopedSlots.default;
    }

    isFilterable () {
        return this.filterable;
    }
}
