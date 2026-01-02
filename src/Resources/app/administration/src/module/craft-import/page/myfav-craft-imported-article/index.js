import template from './index.html.twig';
import './index.scss';

const {Application, Component, Mixin, Service} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('myfav-craft-imported-article', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    computed: {
        myfavCraftImportArticleRepository() {
            return this.repositoryFactory.create('myfav_craft_import_article');
        },

        columns() {
            return [
                { property: 'craftProductNumber', label: 'Craft-Artikel-Nummer' },
                { property: 'craftData.productName', label: 'Craft-Name' },
                { property: 'createdAt', label: 'Erst-Import' },
            ];
        },
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            searchTerm: "",
            result: undefined,
            total: 0,
            isLoading: false,
            limit: 50,
            page: 1,
            criteria:  null,
        }
    },

    created() {
        this.loadItems();
    },

    methods: {
        async searchForSearchTerm() {
            this.page = 1;
            this.loadItems();
        },

        async loadItems() {
            this.isLoading = true;

            this.criteria = new Criteria();
            this.criteria.setPage(this.page);
            this.criteria.setLimit(this.limit);

            this.criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            if(this.searchTerm.length > 0) {
                this.criteria.addFilter(Criteria.contains('name', this.searchTerm));
            }

            const result = await this.myfavCraftImportArticleRepository.search(this.criteria, Shopware.Context.api);
            this.result = result;

            console.log('result: ', this.result);

            this.total = result.total;
            this.isLoading = false;
        },

        onPaginate({ page, limit }) {
            this.page = page;
            this.limit = limit;
            this.loadItems();
        },
    }
});