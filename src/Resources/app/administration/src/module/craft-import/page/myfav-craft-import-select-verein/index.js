import template from './index.html.twig';
import './index.scss';

const {Application, Component, Mixin, Service} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('myfav-craft-import-select-verein', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    props: {
        myfavCraftImportArticleId: {
            type: String,
            required: true,
            default: null,
        },
    },

    computed: {
        importedVereinArticleRepository() {
            return this.repositoryFactory.create('imported_verein_article');
        },
        myfavCraftImportArticleRepository() {
            return this.repositoryFactory.create('myfav_craft_import_article');
        },
        myfavVereineRepository() {
            return this.repositoryFactory.create('myfav_verein');
        },
    },

    watch: {
        myfavCraftImportArticleId() {
            this.loadMyfavCraftImportArticle();
        },
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            myfavCraftImportArticle: null,
            searchTerm: "",
            productToVereinStatus: {},
            vereine: undefined,
            vereineColumns: [
                { property: 'name', label: 'Name', allowResize: true, },
                { property: 'id', label: 'Status', allowResize: true, },
                { property: 'createdAt', label: 'Aktion', allowResize: true, },
            ],
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
        isProductAssignedToVerein(myfavVereinId) {
            if(this.productToVereinStatus.hasOwnProperty(myfavVereinId)) {
                return this.productToVereinStatus[myfavVereinId];
            }

            return null;
        },

        async loadMyfavCraftImportArticle() {
            const [myfavCraftImportArticleResponse] = await Promise.allSettled([
                this.myfavCraftImportArticleRepository.get(
                    this.myfavCraftImportArticleId,
                    Shopware.Context.api,
                    this.myfavCraftImportArticleCriteria),
            ]);

            if (myfavCraftImportArticleResponse.status === 'fulfilled') {
                this.myfavCraftImportArticle = myfavCraftImportArticleResponse.value;
            }

            if (myfavCraftImportArticleResponse.status === 'rejected') {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
            }
        },

        async loadImportedVereinArticles() {
            for(let verein of this.vereine) {
                if(typeof verein === 'undefined') {
                    console.log('ist undefined..');
                    continue;
                }

                console.log('verein: ', verein.id);

                this.criteria = new Criteria();
                this.criteria.addAssociation('myfavVereinArticle');
                this.criteria.setPage(this.page);
                this.criteria.setLimit(this.limit);
                this.criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

                this.criteria.addFilter(
                    Criteria.multi(
                        'AND',
                        [
                            Criteria.equals('myfavVereinArticle.myfavCraftImportArticleId', this.myfavCraftImportArticleId),
                            Criteria.equals('myfavVereinArticle.myfavVereinId', verein.id)
                        ]
                    )
                );

                const result = await this.importedVereinArticleRepository.search(this.criteria, Shopware.Context.api);

                if(result.length > 0) {
                    //this.productToVereinStatus[verein.id] = true;
                    this.$set(this.productToVereinStatus, verein.id, true);
                } else {
                    this.$set(this.productToVereinStatus, verein.id, false);
                }
            }
        },

        async loadItems() {
            await this.loadMyfavCraftImportArticle();
            await this.loadVereine();
            await this.loadImportedVereinArticles();
        },

        async loadVereine() {
            this.criteria = new Criteria();
            this.criteria.setPage(this.page);
            this.criteria.setLimit(this.limit);
            this.criteria.addSorting(Criteria.sort('createdAt', 'DESC'));
            const result = await this.myfavVereineRepository.search(this.criteria, Shopware.Context.api);
            this.vereine = result;
            this.total = result.total;
        },

        onPaginate({ page, limit }) {
            this.page = page;
            this.limit = limit;
            this.loadVereine();
        },

        async searchForSearchTerm() {
            this.page = 1;
            this.loadItems();
        },
    }
});