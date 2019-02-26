<template>
    <div class="data-table-container" :class="{'is-loading': loading}">
        <div class="data-table-overlay" v-if="loading" v-cloak>
            <div class="data-table-loader">
                <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            </div>
        </div>

        <div class="py-4 px-3 flex" v-if="searchable">
            <div class="self-center" v-if="pagination">
                Show

                <select v-model="perPage" @change="changeItemsPerPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                items per page.
            </div>

            <div class="ml-auto">
                <!-- Filter:

                <div v-for="filterableColumn in filterableColumns">
                    {{ filterableColumn.label }}

                    <multiselect
                        v-model="filters[filterableColumn.show]"
                        :options="['casino', 'no exists']"
                        :multiple="true"
                        @input="getResults()">
                    </multiselect>
                </div> -->

                <input
                    type="search"
                    placeholder="Search"
                    class="border rounded-full outline-none px-5 py-2 text-sm w-48"
                    style="background: rgb(250, 250, 250);"
                    @input="isTyping = true"
                    v-model="searchString">
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th v-for="column in columns">
                        <span v-if="!column.sortable">{{ column.label }}</span>
                        <a href="javascript:void(0)" class="no-underline" @click="toggleSortForColumn(column.show)" v-else>
                            <span class="mr-1">{{ column.label }}</span>

                            <i class="fas fa-sort" v-if="columnSortedDirection(column.show) === null"></i>
                            <i class="fas fa-sort-up" v-if="columnSortedDirection(column.show) === 'asc'"></i>
                            <i class="fas fa-sort-down" v-if="columnSortedDirection(column.show) === 'desc'"></i>
                        </a>
                    </th>
                </tr>
            </thead>

            <tbody>
                <data-table-row
                    v-for="row in rows"
                    :key="row.vueTableComponentInternalRowId"
                    :row="row"
                    :columns="columns"
                ></data-table-row>
            </tbody>
        </table>

        <div class="flex px-4 py-2" v-if="pagination">
            <div class="self-center" v-if="getData().length">
                Displaying {{ this.resourceNameSingular }} {{ data.from }} to {{ data.to }} of total {{ data.total }} {{ this.resourceNamePlural }}.
            </div>

            <div class="self-center" v-if="getData().length === 0">
                No items to display.
            </div>

            <div class="ml-auto">
                <pagination :data="data" @pagination-change-page="getResults" :show-disabled="false" :limit="3"></pagination>
            </div>
        </div>

        <div class="px-4 py-2" v-if="getData().length === 0 && !pagination">
            No items to display.
        </div>

        <div style="display:none;">
            <slot></slot>
        </div>
    </div>
</template>

<style lang="scss">
    .data-table-container {
        &.is-loading {
            & .data-table-overlay {
                display: block;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                background-color: rgba(255, 255, 255, .8);
            }

            & .data-table-loader {
                position: absolute;
                left: calc(50% - 32px);
                top: calc(50% - 32px);
                transform: translate(-50%,-50%);
            }
        }
    }
</style>

<script>
    import Column from '../classes/Column';
    import Row from '../classes/Row';

    export default {
        props: {
            query: { type: String, required: true },
            resourceNameSingular: { type: String, default: 'item', },
            resourceNamePlural: { type: String, default: 'items', },
            searchable: { type: Boolean, default: false, },
            itemsPerPage: { type: Number, default: 10, },
            pagination: { type: Boolean, default: false, },
        },

        data () {
            return {
                columns: [],
                filterableColumns: [],
                rows: [],
                loading: false,
                data: {},
                sortings: [],
                filters: [],
                searchString: null,
                isTyping: false,
                perPage: 10,
            }
        },

        watch: {
            searchString: _.debounce(function() {
                this.isTyping = false;
            }, 1000),

            isTyping: function(value) {
                if (!value) {
                    this.search(this.searchString);
                }
            }
        },

        async mounted () {
            await this.getResults();

            this.perPage = this.itemsPerPage;

            const columnComponents = this.$slots.default
                .filter(column => column.componentInstance)
                .map(column => column.componentInstance);

            this.columns = columnComponents.map(
                column => new Column(column)
            );

            // this.filterableColumns = collect(this.columns).filter(column => {
            //     return column.isFilterable();
            // }).toArray();

            // this.filters = collect(this.filterableColumns).mapWithKeys(column => {
            //     return [column.show, []];
            // }).all();

            columnComponents.forEach(columnCom => {
                Object.keys(columnCom.$options.props).forEach(
                    prop => columnCom.$watch(prop, () => {
                        this.columns = columnComponents.map(
                            column => new Column(column)
                        );
                    })
                );
            });

            this.mapDataToRows();
        },

        methods: {
            changeItemsPerPage () {
                this.getResults();
            },

            columnSortedDirection (column) {
                // var current_sorting = collect(this.sortings).where('column', column).first();

                // return (current_sorting === undefined) ? null : current_sorting.value;
            },

            toggleSortForColumn (column) {
                var current_sorting = this.sortings.find(sorting => {
                    return sorting['column'] && sorting['column'] === column;
                });

                if (current_sorting !== undefined && current_sorting.value === 'asc') {
                    current_sorting = undefined;
                    this.sortings.splice(this.sortings.indexOf(current_sorting), 1);
                } else {
                    if (current_sorting === undefined) {
                        this.sortings.push(current_sorting = {
                            column: column,
                            value: 'desc',
                        });
                    } else {
                        var direction = current_sorting.value;

                        this.sortings.splice(this.sortings.indexOf(current_sorting), 1);

                        this.sortings.push(current_sorting = {
                            column: column,
                            value: 'asc',
                        });
                    }
                }

                this.getResults();
            },

            async getResults (page = 1) {
                if (this.loading) {
                    return false;
                }

                this.loading = true;

                let params = {
                    page: page,
                    limit: Number(this.perPage),
                    searchString: this.searchString,
                    filters: this.filters,
                };

                if (this.sortings.length) {
                    var orderings = [];

                    this.sortings.forEach(sorting => {
                        orderings.push((sorting.value === 'desc') ? `-${sorting.column}` : sorting.column);
                    });

                    params['sort'] = orderings.join(',');
                }

                return axios.get(`/querify?query=${this.query}&params=${window.qs.stringify(params)}`).then(response => {
                    this.data = response.data;

                    this.mapDataToRows();

                    this.loading = false;
                });
            },

            getData () {
                return (this.data.data !== undefined && Array.isArray(this.data.data))
                    ? this.data.data
                    : this.data;
            },

            refresh () {
                this.getResults();
            },

            mapDataToRows () {
                let rowId = 0;

                this.rows = this.getData().map(rowData => {
                    rowData.vueTableComponentInternalRowId = rowId++;
                    return rowData;
                }).map(rowData =>
                    new Row(rowData, this.columns)
                );
            },

            search: function(query) {
                this.getResults();
            }
        }
    }
</script>
