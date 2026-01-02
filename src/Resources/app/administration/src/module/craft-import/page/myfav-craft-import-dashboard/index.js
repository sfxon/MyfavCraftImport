import template from './dashboard.html.twig';
import './dashboard.scss';

const {Component, Mixin} = Shopware;

Component.register('myfav-craft-import-dashboard', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            myfavCraftImportArticleCount: 0,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.loadCount();
    },

    methods: {
        async loadCount() {
            const repository = this.repositoryFactory.create('myfav_craft_import_article');

            // Nur zählen, keine Daten laden
            const criteria = new Shopware.Data.Criteria();
            this.myfavCraftImportArticleCount = await repository.searchIds(criteria).then(result => result.total);
        },
    },
});
