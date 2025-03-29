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
            initiallyActivateAllVariants: false,
            productCustomFieldForFabrics: null,
            propertyIdForProductFeature: null,
            propertyIdForProductFit: null,
            propertyIdForProductGender: null,
            showArticleDetailModal: false,
            searchDisabled: false,
            searchResultJson: null,
            searchResultObject: null,
            searchTerm: "",
            selectedSearchResult: null,
            selectedSearchResultVariants: null,
            shopwareProductSettings: {
                customProductBrandId: "", // E.g. Manufacturer.
                customProductCustomFieldForFabrics: "",
                customProductDescription: "",
                customProductFeatures: "",
                customProductFit: "",
                customProductGender: "",
                customProductName: "",
                customProductNumber: "",
                customTaxId: null,
                updateProductBrandId: false,
                updateProductCustomFieldForFabrics: false,
                updateProductCategories: false,
                updateProductDescription: false,
                updateProductFeatures: false,
                updateProductFit: false,
                updateProductGender: false,
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

            // Setup variants.
            this.selectedSearchResultVariants = [];

            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    console.log(variation.skus[k]);

                    let myfavCraftSettings = {
                        activated: this.initiallyActivateAllVariants
                    };

                    variation.skus[k].myfavCraftSettings = myfavCraftSettings;

                    //variation.skus[k].myfavCraftSettings.activated = this.initiallyActivateAllVariants;
                }
            }
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
            this.productCustomFieldForFabrics = pluginConfig['MyfavCraftImport.config.productCustomFieldForFabrics'];
            this.propertyIdForProductFeature = pluginConfig['MyfavCraftImport.config.propertyIdForProductFeature'];
            this.propertyIdForProductFit = pluginConfig['MyfavCraftImport.config.propertyIdForProductFit'];
            this.propertyIdForProductGender = pluginConfig['MyfavCraftImport.config.propertyIdForProductGender'];
            this.initiallyActivateAllVariants = pluginConfig['MyfavCraftImport.config.initiallyActivateAllVariants'];
            this.searchResultObject = JSON.parse(searchResult.data.data);
            this.searchResultJson = JSON.stringify(this.searchResultObject , null, 2);

            if(this.productCustomFieldForFabrics !== null) {
                this.productCustomFieldForFabrics = this.productCustomFieldForFabrics.trim();

                if(this.productCustomFieldForFabrics.length === 0) {
                    this.productCustomFieldForFabrics = null;
                }
            }

            if(this.initiallyActivateAllVariants === null) {
                this.initiallyActivateAllVariants = false;
            }

            // Eingabefelder nach dem Ladevorgang wieder aktivieren.
            this.searchDisabled = false;
        },

        selectProductBrandId(id, item) {
            this.shopwareProductSettings.customProductBrand = id;
        },

        selectTaxRate(id, item) {
            this.shopwareProductSettings.customTaxId = id;
        }
    }
});