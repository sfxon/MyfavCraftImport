import template from './article.html.twig';
import './article.scss';
import CraftProductSearchApiService from "./../../service/api/craft-product-search.api.service.js";

const {Application, Component, Mixin, Service} = Shopware;

Component.register('myfav-craft-import-article', {
    template,

    inject: ['systemConfigApiService'],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            articleNumber: "",
            craftProductSearchApiService: null,
            pluginConfig: null,
            searchDisabled: false
        }
    },

    methods: {
        async searchForArticleNumber() {
            // Eingabefelder während des Ladevorgangs deaktivieren.
            this.searchDisabled = true;

            // Daten über die API laden.
            if(this.craftProductSearchApiService == null) {
                const httpClient = Application.getContainer('init')['httpClient'];
                const loginService = Service('loginService');

                this.craftProductSearchApiService = new CraftProductSearchApiService(
                    httpClient,
                    loginService
                );
            }

            const [pluginConfig, searchResult] = await Promise.all([
                this.systemConfigApiService.getValues('MyfavCraftImport.config'),
                this.craftProductSearchApiService.search(this.articleNumber)
            ]);

            console.log('pluginConfig: ', pluginConfig['MyfavCraftImport.config.debugMode']);
            console.log('searchResult: ', JSON.parse(searchResult.data.data));

            // Eingabefelder nach dem Ladevorgang wieder aktivieren.
            this.searchDisabled = false;
        },
    }
});
