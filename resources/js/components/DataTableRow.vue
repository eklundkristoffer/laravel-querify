<template>
    <tr>
        <table-cell
            v-for="column in visibleColumns"
            :row="row"
            :column="column"
            :key="column.id"
        ></table-cell>
    </tr>
</template>

<script>
    import TableCell from '../TableCell';

    export default {
        props: ['columns', 'row'],

        components: {
            TableCell,
        },

        computed: {
            visibleColumns() {
                return this.columns;
            },
        },
    };
</script>
