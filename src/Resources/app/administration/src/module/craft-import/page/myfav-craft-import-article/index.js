import template from './article.html.twig';
import './article.scss';
import CraftProductSearchApiService from "./../../service/api/craft-product-search.api.service.js";

const {Application, Component, Mixin, Service} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('myfav-craft-import-article', {
    template,

    inject: ['repositoryFactory', 'systemConfigApiService'],

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
            // Erstelle initial eine leere categoryCollection als EntityCollection.
            // Das ist wichtig, da sonst keine Kategorien in der Kategorie-Auswahl-Liste angezeigt werden.
            categoryCollection: new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []),
            craftProductSearchApiService: null,
            debugMode: false,
            showArticleDetailModal: false,
            searchDisabled: false,
            searchResultJson: null,
            searchResultObject: null,
            searchTerm: "",
            selectedSearchResult: null,
            shopwareProductSettings: {
                customProductDescription: "",
                customProductName: "",
                customProductNumber: "",
                customTaxId: null,
                updateProductCategories: false,
                updateProductDescription: false,
                updateProductFromCraftApi: true,
                updateProductName: false,
                updateProductNumber: false,
                updateTaxId: false
            }
        }
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        categoryCriteria() {
            const criteria = new Criteria(1, 500);
            return criteria;
        },
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
            this.shopwareProductSettings.customTaxId = pluginConfig['MyfavCraftImport.config.defaultTaxId'];
            this.searchResultObject = JSON.parse(searchResult.data.data);
            this.searchResultJson = JSON.stringify(this.searchResultObject , null, 2);

            // Eingabefelder nach dem Ladevorgang wieder aktivieren.
            this.searchDisabled = false;
        },

        selectTaxRate(id, item) {
            this.shopwareProductSettings.customTaxId = id;
        }
    }
});