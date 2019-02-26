<template>
    <div v-if="false">
        <slot></slot>
    </div>
</template>

<script>
    export default {
        props: {
            label: { required: false, type: String },
            show: { required: false, type: String },
            formatter: { default: v => v, type: Function },
            sortable: { default: false, type: Boolean },
            filterable: { default: false, type: Boolean },
        }
    }
</script>
