import template from './list.html.twig';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('myfav-craft-verein-list', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            myfavAllowEdit: true,
            entitySearchable: true,
            myfavVereine: [],
            myfavVereineColumns: [
                { property: 'name', label: 'Name', allowResize: true, },
            ],
            isLoading: true,
            page: 1,
            sortBy: 'name',
            sortDirection: 'ASC',
            total: 0,
            searchConfigEntity: 'myfav_verein',
            limit: 100
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        myfavVereinRepository() {
            return this.repositoryFactory.create('myfav_verein');
        },

        myfavVereinCriteria() {
            const myfavVereinCriteria = new Criteria(this.page, this.limit);

            myfavVereinCriteria.setTerm(this.term);
            myfavVereinCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));

            return myfavVereinCriteria;
        },
    },

    methods: {
        deleteVerein(id) {
            if(confirm('Wollen Sie diesen Eintrag wirklich löschen?')) {
                this.myfavVereinRepository.delete(id, Shopware.Context.api).then(() => {
                    this.getList();
                });
            }
        },

        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.myfavVereinCriteria);

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.myfavVereinRepository.search(criteria)
                .then(searchResult => {
                    this.myfavVereine = [];

                    for(let i = 0, j = searchResult.length; i < j; i++) {
                        this.myfavVereine.push({
                            id: searchResult[i].id,
                            name: searchResult[i].name,
                        });
                    }

                    this.total = searchResult.total;
                    this.isLoading = false;
                });
        },

        updateTotal({ total }) {
            this.total = total;
        },
    },
});
