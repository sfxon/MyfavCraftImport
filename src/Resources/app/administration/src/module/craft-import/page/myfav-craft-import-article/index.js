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
            searchTerm: "",
            craftProductSearchApiService: null,
            debugMode: false,
            pluginConfig: null,
            showArticleDetailModal: false,
            searchDisabled: false,
            searchResultJson: null,
            searchResultObject: null,
            selectedSearchResult: null,
            shopwareProductSettings: {
                updateProductFromCraftApi: true,
                updateProductName: false,
                customProductName: "test"
            }
        }
    },

    methods: {
        getSearchResultAsString() {
            return this.searchResultJson // Reformat the json.
        },

        onCloseArticleDetailModal() {
            this.showArticleDetailModal = false;
        },

        onShowArticleDetailModal(item) {
            this.selectedSearchResult = item;
            this.showArticleDetailModal = true;
        },

        async searchForSearchTerm() {
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
                this.craftProductSearchApiService.search(this.searchTerm)
            ]);

            this.debugMode = pluginConfig['MyfavCraftImport.config.debugMode'];
            this.searchResultObject = JSON.parse(searchResult.data.data);
            this.searchResultJson = JSON.stringify(this.searchResultObject , null, 2);

            // Eingabefelder nach dem Ladevorgang wieder aktivieren.
            this.searchDisabled = false;
        },
    }
});