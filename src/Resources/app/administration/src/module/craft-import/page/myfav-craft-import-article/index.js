import template from './article.html.twig';
import './article.scss';
import CraftProductImageSaveApiService from "./../../service/api/craft-product-image-save.api.service.js";
import CraftProductSaveApiService from "./../../service/api/craft-product-save.api.service.js";
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
            craftProductImageSaveApiService: null,
            craftProductSaveApiService: null,
            craftProductSearchApiService: null,
            errorText: '',
            debugMode: false,
            initiallyActivateAllVariants: false,
            isSavingArticle: false,
            lastSearchResultAsString: '',
            maxProductNumberLength: 60,
            productCustomFieldForFabrics: null,
            propertyIdForProductFeature: null,
            propertyIdForProductFit: null,
            propertyIdForProductGender: null,
            savedSuccessfully: false,
            saveStatusLog: "",
            showArticleDetailModal: false,
            searchDisabled: false,
            searchResultJson: null,
            searchResultObject: null,
            searchTerm: "",
            selectedSearchResult: null,
            selectedSearchResultVariants: null,
            shopwareProductSettings: {
                configurationId: null, // In support for Neonlines product configurator.
                currentCalculatedProcentualPrice: 0.0,
                customProductBrandId: "", // E.g. Manufacturer.
                customProductCategories: "",
                customProductCustomFieldForFabrics: "",
                customProductDescription: "",
                customProductFeatures: "",
                customProductFit: "",
                customProductGender: "",
                customProductName: "",
                customProductNumber: "",
                customProductPriceGros: "",
                customTaxId: null,
                discountInPercent: 0,
                priceWarning: false,
                productNumberLength: 0,
                updateProductBrandId: false,
                updateProductCustomFieldForFabrics: false,
                updateProductCategories: false,
                updateProductDescription: false,
                updateProductFeatures: false,
                updateProductFit: false,
                updateProductGender: false,
                updateProductFromCraftApi: true,
                updateProductName: false,
                updateProductNumber: true,
                updateProductPriceGros: false,
                updateTaxId: false,
                usePercentualDiscount: false,
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
        onChangedVariantProcentualDiscount(variationSku, newValue) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.discountInPercent = newValue;
                        this.$set(variation.skus, k, skuData);
                        this.recalculateVariantProcentualPrice(variationSku);
                    }
                }
            }
        },

        onCloseArticleDetailModal() {
            this.showArticleDetailModal = false;
        },

        async onShowArticleDetailModal(item) {
            // Artikelnummer konfigurieren.
            this.shopwareProductSettings.customProductNumber = await this.generateProductNumber(item.productName);

            // Setup variants.
            this.selectedSearchResultVariants = [];
            let productNumber = 1;

            for(let i = 0, j = item.variations.length; i < j; i++) {
                let variation = item.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let myfavCraftSettings = {
                        activated: this.initiallyActivateAllVariants,
                        currentCalculatedProcentualPrice: 0.0,
                        customStockValue: variation.skus[k].availabilityGlobal,
                        discountInPercent: 0,
                        productNumber: "" + this.shopwareProductSettings.customProductNumber + "." + productNumber,
                        priceGros: item.retailPrice.price,
                        priceWarning: false,
                        updateCustomStockNow: false,
                        useCustomPrice: false,
                        useCustomStock: false,
                        usePercentualDiscount: false,
                    };

                    variation.skus[k].myfavCraftSettings = myfavCraftSettings;
                    productNumber = productNumber + 1;
                }
            }

            this.selectedSearchResult = item;
            this.showArticleDetailModal = true;
        },

        finishArticleSavingProcess() {
            this.savedSuccessfully = false;
            this.isSavingArticle = false;
            this.saveStatusLog = '';
        },

        async generateProductNumber(productName) {
                let productNumber = productName.replace(/^\s+|\s+$/g, ''); // Trim outer white-spaces.
                productNumber = productNumber
                    .replace(/[^A-Za-z0-9 -]/g, '') // Remove non-alphanumeric characters.
                    .replace(/\s+/g, '-') // Replace spaces with hyphens.
                    .replace(/-+/g, '-'); // Remove consecutive hyphens.
                productNumber = productNumber.substr(0, 60);

                return productNumber;
        },

        getFormattedPrice(price) {
            if(isNaN(price)) {
                return 0.0;
            }

            if(price === 0 || price === 0.0) {
                return 0.0;
            }

            return (Math.round(price * 100) / 100);
        },

        getVariantImagesByProductNumber(productNumber) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let skuData = variation.skus[k];

                    if(productNumber === skuData.myfavCraftSettings.productNumber) {
                        let imageUrls = [];

                        for(let m = 0, n = variation.pictures.length; m < n; m++) {
                            imageUrls.push(variation.pictures[m].imageUrl);
                        }

                        return imageUrls;
                    }
                }
            }

            return [];
        },
        
        preventModalCloseOnOutsideClick() {
        },

        recalculateProcentualPrice() {
            this.$set(this.shopwareProductSettings, 'priceWarning', false);

            // Check, if the price from Craft is valid.
            let priceGros = this.selectedSearchResult.retailPrice.price;

            if(isNaN(priceGros)) {
                this.$set(this.shopwareProductSettings, 'currentCalculatedProcentualPrice', this.getFormattedPrice(0.0));
                this.$set(this.shopwareProductSettings, 'priceWarning', true);
                return;
            }

            // Check, if percentual discount is activated.
            let usePercentualDiscount = this.shopwareProductSettings.usePercentualDiscount;

            if(usePercentualDiscount === false) {
                return;
            }

            // Check, if percentual value is valid.
            let discountInPercent = this.shopwareProductSettings.discountInPercent;

            if(typeof(discountInPercent) === 'string') {
                discountInPercent = discountInPercent.replace(',', '.');
            }

            discountInPercent = parseFloat(discountInPercent);

            if(isNaN(discountInPercent)) {
                this.$set(this.shopwareProductSettings, 'currentCalculatedProcentualPrice', this.getFormattedPrice(0.0));
                this.$set(this.shopwareProductSettings, 'priceWarning', true);
                return;
            }

            if(discountInPercent == 0.0 || priceGros == 0.0) {
                this.$set(this.shopwareProductSettings, 'currentCalculatedProcentualPrice', this.getFormattedPrice(priceGros));
            } else {
                this.$set(this.shopwareProductSettings, 'currentCalculatedProcentualPrice', this.getFormattedPrice((priceGros - (priceGros / 100 * discountInPercent))));
            }
        },

        recalculateVariantProcentualPrice(variationSku) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];

                        skuData.myfavCraftSettings.priceWarning = false;
                        this.$set(variation.skus, k, skuData);

                        // Check, if the price from Craft is valid.
                        let priceGros = skuData.retailPrice.price;

                        if(isNaN(priceGros)) {
                            skuData.myfavCraftSettings.currentCalculatedProcentualPrice = this.getFormattedPrice(0.0);
                            skuData.myfavCraftSettings.priceWarning = true;
                            this.$set(variation.skus, k, skuData);
                            return;
                        }

                        // Check, if percentual discount is activated.
                        let usePercentualDiscount = skuData.myfavCraftSettings.usePercentualDiscount;

                        if(usePercentualDiscount === false) {
                            return;
                        }

                        // Check, if percentual value is valid.
                        let discountInPercent = skuData.myfavCraftSettings.discountInPercent;

                        if(typeof(discountInPercent) === 'string') {
                            discountInPercent = discountInPercent.replace(',', '.');
                        }

                        discountInPercent = parseFloat(discountInPercent);

                        if(isNaN(discountInPercent)) {
                            skuData.myfavCraftSettings.currentCalculatedProcentualPrice = this.getFormattedPrice(0.0);
                            skuData.myfavCraftSettings.priceWarning = true;
                            this.$set(variation.skus, k, skuData);
                            return;
                        }

                        if(discountInPercent == 0.0 || priceGros == 0.0) {
                            skuData.myfavCraftSettings.currentCalculatedProcentualPrice = this.getFormattedPrice(priceGros);
                            this.$set(variation.skus, k, skuData);
                        } else {
                            skuData.myfavCraftSettings.currentCalculatedProcentualPrice = this.getFormattedPrice((priceGros - (priceGros / 100 * discountInPercent)));
                            this.$set(variation.skus, k, skuData);
                        }
                    }
                }
            }
        },

        async saveProduct(syncProduct) {
            this.isSavingArticle = true;
            this.saveStatusLog = '';

            // Ausgewählte Kategorien zur Übertragung übernehmen.
            this.shopwareProductSettings.customProductCategories = this.categoryCollection.getIds();

            // Daten über die API speichern.
            if(this.craftProductSaveApiService == null) {
                const httpClient = Application.getContainer('init')['httpClient'];
                const loginService = Service('loginService');

                this.craftProductSaveApiService = new CraftProductSaveApiService(
                    httpClient,
                    loginService
                );
            }

            this.saveStatusLog += 'Artikeldaten speichern: ';

            let [savedProductDataDto] = await Promise.all([
                this.craftProductSaveApiService.save(this.selectedSearchResult, this.shopwareProductSettings, syncProduct)
            ]);

            if(savedProductDataDto.data.status !== 'success') {
                this.saveStatusLog += '<br /><b style="color: red;">Fehler:</b></br>';
                this.saveStatusLog += savedProductDataDto.errorMessage;
                return;
            }

            if(syncProduct) {
                this.saveProductImages(savedProductDataDto);
            }
        },

        async saveProductImages(savedProductDataDto) {
            this.saveStatusLog += '&check;<br />Bilder speichern:';
            this.saveStatusLog += ' Hauptartikel';

            if(this.craftProductImageSaveApiService == null) {
                const httpClient = Application.getContainer('init')['httpClient'];
                const loginService = Service('loginService');

                this.craftProductImageSaveApiService = new CraftProductImageSaveApiService(
                    httpClient,
                    loginService
                );
            }

            // Get the images for the main product.
            let imageUrls = [];
            let pictures = this.selectedSearchResult.pictures;

            for(let i = 0, j = pictures.length; i < j; i++) {
                imageUrls.push(pictures[i].imageUrl);
            }

            let [saveResult] = await Promise.all([
                this.craftProductImageSaveApiService.save(
                    savedProductDataDto.data.mainProductData.id,
                    savedProductDataDto.data.mainProductData.productNumber,
                    imageUrls)
            ]);

            this.saveStatusLog += ' &check;';

            let variantData = savedProductDataDto.data.variantProductData;

            if(Array.isArray(variantData)) {
                // Varianten-Bilder speichern.
                for(let i = 0, j = variantData.length; i < j; i++) {
                    imageUrls = this.getVariantImagesByProductNumber(variantData[i].productNumber);

                    this.saveStatusLog += ', ' + variantData[i].productNumber + ' (' + imageUrls.length + ')';

                    if(imageUrls.length > 0) {
                        await Promise.all([
                            this.craftProductImageSaveApiService.save(
                                variantData[i].id,
                                variantData[i].productNumber, // Is not required, but please keep it, because if it is provided, things are easier to debug.
                                imageUrls
                            )
                        ]);
                    }

                    this.saveStatusLog += ' &check;';
                }
            }

            this.saveStatusLog += '<br /><br />Speichern erfolgreich abgeschlossen.';
            this.savedSuccessfully = true;
        },

        async searchForSearchTerm() {
            // Eingabefelder während des Ladevorgangs deaktivieren.
            this.searchDisabled = true;
            this.errorText = '';

            // Daten über die API laden.
            if(this.craftProductSearchApiService == null) {
                const httpClient = Application.getContainer('init')['httpClient'];
                const loginService = Service('loginService');

                this.craftProductSearchApiService = new CraftProductSearchApiService(
                    httpClient,
                    loginService
                );
            }

            let pluginConfig = null;
            let searchResult =  '';

            try {
                [pluginConfig, searchResult] = await Promise.all([
                    this.systemConfigApiService.getValues('MyfavCraftImport.config'),
                    this.craftProductSearchApiService.search(this.searchTerm)
                ]);
            } catch(e) {
                this.searchResultObject = null;
                this.searchResultJson = '';
                this.searchDisabled = false;

                if (e.response && e.response.data && e.response.data.errors) {
                    // Shopware-Fehler mit API-Fehlerstruktur
                    this.errorText = e.response.data.errors[0]?.detail || 'Unbekannter API-Fehler';
                } else if (e.message) {
                    this.errorText = e.message;
                } else {
                    this.errorText = 'Ein unbekannter Fehler ist aufgetreten.';
                }

                throw(e);
            }

            this.debugMode = pluginConfig['MyfavCraftImport.config.debugMode'];
            this.shopwareProductSettings.customTaxId = pluginConfig['MyfavCraftImport.config.defaultTaxId'];
            this.productCustomFieldForFabrics = pluginConfig['MyfavCraftImport.config.productCustomFieldForFabrics'];
            this.propertyIdForProductFeature = pluginConfig['MyfavCraftImport.config.propertyIdForProductFeature'];
            this.propertyIdForProductFit = pluginConfig['MyfavCraftImport.config.propertyIdForProductFit'];
            this.propertyIdForProductGender = pluginConfig['MyfavCraftImport.config.propertyIdForProductGender'];
            this.initiallyActivateAllVariants = pluginConfig['MyfavCraftImport.config.initiallyActivateAllVariants'];
            
            try {
                this.searchResultObject = JSON.parse(searchResult.data.data);
                this.searchResultJson = JSON.stringify(this.searchResultObject , null, 2);
                this.lastSearchResultAsString = this.searchResultJson;

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
            } catch(e) {
                this.searchResultObject = null;
                this.searchResultJson = '';

                if(searchResult.hasOwnProperty('data') && searchResult.data.hasOwnProperty('data')) {
                    this.errorText = searchResult.data.data;
                    this.errorText += "\n-----------\nFehlermeldung:\n";
                    this.errorText += e.message;
                } else {
                    this.errorText = e.message;
                }
                this.searchDisabled = false;
                throw(e);
            }
        },

        selectConfiguration(id, item) {
            this.shopwareProductSettings.configurationId = id;
        },

        selectProductBrandId(id, item) {
            this.shopwareProductSettings.customProductBrand = id;
        },

        selectTaxRate(id, item) {
            this.shopwareProductSettings.customTaxId = id;
        },

        // Event-Handler: Wenn die Checkbox "Prozentuale Preis-Berechnung" für den Haupt-Artikel-Teil aktiviert/deaktiviert wurde.
        updateUsePercentualDiscountForMainProduct(newValue) {
            this.shopwareProductSettings.usePercentualDiscount = newValue;
            this.recalculateProcentualPrice();
        },

        // Event-Handler: Wenn die Checkbox "Prozentuale Preis-Berechnung" für eine Variante aktiviert/deaktiviert wurde.
        updateUsePercentualDiscountForVariant(variationSku, newValue) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.usePercentualDiscount = newValue;
                        this.$set(variation.skus, k, skuData);
                        this.recalculateVariantProcentualPrice(variationSku);
                    }
                }
            }
        },

        updateProductNumber(newNumber) {
            this.shopwareProductSettings.productNumberLength = newNumber.length;
        },

        updateVariantActivated(variationSku, activated) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.activated = activated;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        activateVariantsSkus(clickedVariation, activated) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let skuData = variation.skus[k];

                    for(let x = 0, y = clickedVariation.skus.length; x < y; x++) {
                        let cvSku = clickedVariation.skus[x];

                        if(cvSku.sku === skuData.sku) {
                            skuData.myfavCraftSettings.activated = activated;
                            this.$set(variation.skus, k, skuData);
                        }
                    }
                }
            }
        },

        updateVariantArticleNumber(variationSku, newProductNumber) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.productNumber = newProductNumber;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        updateVariantArticleNumbers() {
            let productNumber = 1;

            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let skuData = variation.skus[k];
                    skuData.myfavCraftSettings.productNumber = this.shopwareProductSettings.customProductNumber + '.' + productNumber;
                    this.$set(variation.skus, k, skuData);
                    productNumber++;
                }
            }
        },

        updateVariantArticlePrice(variationSku, newPrice) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.priceGros = newPrice;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        updateVariantArticlePrices() {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let skuData = variation.skus[k];

                    // skuData.myfavCraftSettings.currentCalculatedProcentualPrice = this.shopwareProductSettings.currentCalculatedProcentualPrice;
                    skuData.myfavCraftSettings.priceGros = this.shopwareProductSettings.customProductPriceGros;
                    skuData.myfavCraftSettings.discountInPercent = this.shopwareProductSettings.discountInPercent;
                    skuData.myfavCraftSettings.useCustomPrice = this.shopwareProductSettings.updateProductPriceGros;
                    skuData.myfavCraftSettings.usePercentualDiscount = this.shopwareProductSettings.usePercentualDiscount;

                    this.$set(variation.skus, k, skuData);

                    this.recalculateVariantProcentualPrice(skuData);
                }
            }
        },

        updateVariantUseCustomPrice(variationSku, useCustomPrice) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.useCustomPrice = useCustomPrice;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        updateVariantUseCustomStock(variationSku, useCustomStock) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.useCustomStock = useCustomStock;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        updateUpdateCustomStockNowForVariant(variationSku, updateCustomStockNow) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.updateCustomStockNow = updateCustomStockNow;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        updateCustomStockValue(variationSku, customStockValue) {
            for(let i = 0, j = this.selectedSearchResult.variations.length; i < j; i++) {
                let variation = this.selectedSearchResult.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.customStockValue = customStockValue;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },
    }
});