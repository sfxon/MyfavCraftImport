import template from './index.html.twig';
import './index.scss';
import CraftImportedArticleSaveApiService from "./../../service/api/craft-imported-article-save.api.service.js";

const {Application, Component, Mixin, Service} = Shopware;
const { Criteria } = Shopware.Data;

Component.register('myfav-craft-import-assign-verein', {
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
        myfavVereinId: {
            type: String,
            required: true,
            default: null,
        },
    },

    computed: {
        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        categoryCriteria() {
            const criteria = new Criteria(1, 500);
            return criteria;
        },
        myfavCraftImportArticleRepository() {
            return this.repositoryFactory.create('myfav_craft_import_article');
        },
        myfavVereinRepository() {
            return this.repositoryFactory.create('myfav_verein');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        },
    },

    watch: {
        myfavCraftImportArticleId() {
            this.loadMyfavCraftImportArticle();
        },
        myfavVereinId() {
            this.loadMyfavVerein();
        },
    },

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
            craftImportedArticleSaveApiService: null,
            criteria:  null,
            customProductNumberErrorMessage: null,
            debugMode: false,
            duplicateVariantProductNumbers: [],
            generalErrorMessage: false,
            isActionBarVisible: true,
            isLoading: true,
            isSaveSuccessful: false,
            isSavingArticle: false,
            initiallyActivateAllVariants: false,
            limit: 50,
            maxProductNumberLength: 60,
            myfavCraftImportArticle: null,
            myfavCraftImportArticleCategoryCollection: new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []),
            myfavVerein: null,
            page: 1,
            productCustomFieldForFabrics: null,
            propertyIdForProductFeature: null,
            propertyIdForProductFit: null,
            propertyIdForProductGender: null,
            saveResult: {
                data: {},
                errorMessages: "",
                hasError: false
            },
            saveStatusLog: "",
            searchTerm: "",
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
                productId: null,
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
            },
            total: 0,
            verein: undefined,
        }
    },

    created() {
        this.loadItems();
    },

    methods: {
        /**
         * activateVariantsSkus
         */
        activateVariantsSkus(clickedVariation, activated) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

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

        /**
         * findArticleNumberForForeignArticle
         * Finde Artikel, die die angegebene Artikelnummer haben, aber nicht die angegebene productId.
         */
        async articleNumberInForeignArticleExists(productId, articleNumber) {
            const criteria = new Shopware.Data.Criteria();

            // Suche Artikel nach Artikelnummer
            criteria.addFilter(
                Criteria.equals('productNumber', articleNumber)
            );

            // UND: falls eine ID ist, nur die Einträge, die nicht der aktuelle Artikel sind.
            if(productId !== null) {
                criteria.addFilter(
                    Criteria.not('AND', [Criteria.equals('id', productId)])
                );
            }

            const [tmpProductResponse] = await Promise.allSettled([
                this.productRepository.search(
                    criteria,
                    Shopware.Context.api,
                )
            ]);

            if (tmpProductResponse.value !== null && tmpProductResponse.value.total > 0) {
                // Es gibt bereits einen Eintrag mit diesem Token
                return true;
            }

            return false;
        },

        /**
         * checkForDoubleProductNumbers
         */
        async checkForDoubleProductNumbers() {
            if(
                await this.articleNumberInForeignArticleExists(
                    this.shopwareProductSettings.productId,
                    this.shopwareProductSettings.customProductNumber
                )
            ) {
                this.customProductNumberErrorMessage = new Shopware.Classes.ShopwareError({
                    code: 'DUPLICATE_PRODUCT_NUMBER',
                    detail: 'Diese Produktnummer existiert bereits.',
                });
            } else {
                this.customProductNumberErrorMessage = null;
            }
        },

        /**
         * getFormattedPrice
         */
        getFormattedPrice(price) {
            if(isNaN(price)) {
                return 0.0;
            }

            if(price === 0 || price === 0.0) {
                return 0.0;
            }

            return (Math.round(price * 100) / 100);
        },

        isDuplicateProductNumber(productNumber) {
            if(this.duplicateVariantProductNumbers.includes(productNumber)) {
                return new Shopware.Classes.ShopwareError({
                        code: 'DUPLICATE_PRODUCT_NUMBER',
                        detail: 'Diese Produktnummer existiert bereits.',
                    })
                ;
            }

            return null;
        },

        /**
         * loadItems
         */
        async loadItems() {
            await this.loadPluginConfig();
            await this.loadMyfavCraftImportArticle();
            await this.loadVerein();

            // Generiere Artikelnummer, falls sie nicht geladen wurde, bzw. leer ist.
            this.shopwareProductSettings.customProductNumber = 
                this.myfavVerein.productNumberToken +
                '-' +
                this.myfavCraftImportArticle.customData.customProductNumber;

            // Stelle sicher, dass Artikelnummer nur 64 Zeichen lang ist.
            this.shopwareProductSettings.customProductNumber =
                this.shopwareProductSettings.customProductNumber.substr(0, 64);

            // Doubletten-Suche hier.
            this.checkForDoubleProductNumbers();

            // Lade Haupt-Kategorie des Vereins und setze diese als initiale Kategorie
            await this.loadVereinCategory();
            await this.loadMyfavCraftImportArticleCategory();

            this.isLoading = false;
        },

        /**
         * Lade alle Kategorien für die Kategorie-Darstellung
         * -> das hier ist für die Spalte customData - also die Auswahl die man zuvor
         *    beim Importieren des Artikels getroffen hat.
         */
        async loadMyfavCraftImportArticleCategory() {
            const categoryIds = this.myfavCraftImportArticle.customData.customProductCategories;

            if (!Array.isArray(categoryIds) || categoryIds.length === 0) {
                return;
            }

            // Neues Criteria Objekt
            const criteria = new Shopware.Data.Criteria(1, 500);
            criteria.addFilter(
                Criteria.equalsAny('id', categoryIds)
            );

            // Kategorien laden
            const categories = await this.categoryRepository.search(criteria, Shopware.Context.api);

            // EntityCollection direkt aus dem Ergebnis verwenden
            this.myfavCraftImportArticleCategoryCollection = categories;
        },

        async loadPluginConfig() {
            let pluginConfig = null;

            try {
                [pluginConfig] = await Promise.all([
                    this.systemConfigApiService.getValues('MyfavCraftImport.config'),
                ]);
            } catch(e) {
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
        },

        /**
         * Lade die Haupt-Kategorie des Vereins.
         * -> das hier ist für die Spalte in der man den aktuell gewünschten Wert einträgt.
         */
        async loadVereinCategory() {
            // Kategorie laden
            const [categoryResponse] = await Promise.allSettled([
                this.categoryRepository.get(
                    this.myfavVerein.categoryId,
                    Shopware.Context.api,
                    this.categoryCriteria
                )
            ]);

            if(categoryResponse.status === 'fulfilled' && categoryResponse.value !== null) {
                // Reset the collection, to avoid double data.
                this.categoryCollection = new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []);

                // Add new data in collection.
                this.categoryCollection.push(categoryResponse.value);
            }
        },

        /**
         * loadMyfavCraftImportArticle
         */
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

        /**
         * loadVerein
         */
        async loadVerein() {
            const [myfavVereinResponse] = await Promise.allSettled([
                this.myfavVereinRepository.get(
                    this.myfavVereinId,
                    Shopware.Context.api,
                    this.myfavVereinCriteria),
            ]);

            if (myfavVereinResponse.status === 'fulfilled') {
                this.myfavVerein = myfavVereinResponse.value;
            }

            if (myfavVereinResponse.status === 'rejected') {
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationLoadingDataErrorMessage',
                    ),
                });
            }
        },

        /**
         * onCancel
         */
        onCancel() {
            this.$router.push({ name: 'myfav.craft.import.selectVerein', params: { myfavCraftImportArticleId: this.myfavCraftImportArticleId } });
        },

        /**
         * onChangedVariantProcentualDiscount
         */
        onChangedVariantProcentualDiscount(variationSku, newValue) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

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

        /**
         * onSave
         */
        async onSave() {
            this.isLoading = true;
            this.isSaveSuccessful = false;
            this.isSavingArticle = true;
            this.saveStatusLog = '';

            // Ausgewählte Kategorien zur Übertragung übernehmen.
            this.shopwareProductSettings.customProductCategories = this.categoryCollection.getIds();

            // API-Service zum Speichern vorbereiten.
            if(this.craftImportedArticleSaveApiService == null) {
                const httpClient = Application.getContainer('init')['httpClient'];
                const loginService = Service('loginService');

                this.craftImportedArticleSaveApiService = new CraftImportedArticleSaveApiService(
                    httpClient,
                    loginService
                );
            }

            this.saveStatusLog += 'Artikeldaten speichern: ';

            let [savedProductDataDto] = await Promise.all([
                this.craftImportedArticleSaveApiService.save(
                    this.myfavVereinId,
                    this.myfavCraftImportArticleId,
                    this.shopwareProductSettings,
                    this.myfavCraftImportArticle.customData,
                    this.myfavCraftImportArticle.craftData.variations
                )
            ]);

            console.log('savedProductDataDto.data.hasError', savedProductDataDto.data.hasError);

            if(savedProductDataDto.data.hasError === true) {
                this.saveResult = savedProductDataDto.data;

                if(this.saveResult.hasOwnProperty('data') && this.saveResult.data.hasOwnProperty('invalidProductNumbersData')) {
                    // Allgemeine Fehlermeldung oben anzeigen, damit man direkt sieht, dass Fehler aufgetreten sind.
                    this.generalErrorMessage = this.saveResult.errorMessages.join("<br />");

                    // Prüfen, ob die Haupt-Artikelnummer in den fehlerhaften Daten vorkommt. Falls ja, dieses Feld kennzeichnen.
                    let mainArticleProductNumber = this.shopwareProductSettings.customProductNumber;
                    this.duplicateVariantProductNumbers = [];

                    for(let tmpIndex in this.saveResult.data.invalidProductNumbersData) {
                        // Extrahiere übermittelte Artikelnummer.
                        let invalidProductNumbersData = this.saveResult.data.invalidProductNumbersData[tmpIndex];
                        let tmpProductNumber = invalidProductNumbersData['productNumber'];
                        
                        // Vergleiche Artikelnummer mit Haupt-Artikelnummer.
                        if(tmpProductNumber === mainArticleProductNumber) {
                            this.customProductNumberErrorMessage = null;
                            this.customProductNumberErrorMessage = new Shopware.Classes.ShopwareError({
                                code: 'DUPLICATE_PRODUCT_NUMBER',
                                detail: 'Diese Produktnummer existiert bereits.',
                            });
                        } else {
                            this.duplicateVariantProductNumbers.push(tmpProductNumber);
                        }
                    }
                } else {
                    console.log(this.saveResult);
                    this.saveStatusLog += '<br /><b style="color: red;">Fehler:</b></br>';
                    this.saveStatusLog += savedProductDataDto.data.errorMessage;
                    return;
                }

                this.isLoading = false;
                this.isSaveSuccessful = true;
                this.isSavingArticle = false;
                return;
            }

            // Do not save images yet.
            /*
            //if(syncProduct) {
                this.saveProductImages(savedProductDataDto);
            //}
            */

            let tmpData = savedProductDataDto.data.data.addedProducts.mainProductData;
            let tmpMainProductId = tmpData.id;

            console.log('tmpMainProductId', tmpMainProductId);

            this.saveStatusLog += '<br /><span style="color: #46A600">Speichern erfolgreich</span><br /><br /><b>Folgende Artikel wurden angelegt:</b><br />';
            this.saveStatusLog += 
                'Haupt-Artikel: <a href="' +
                    this.$router.resolve({ name: 'sw.product.detail', params: { id: tmpMainProductId } }).href +
                '" target="_blank">' +
                    tmpData.productNumber + 
                '</a><br />';

            tmpData = savedProductDataDto.data.data.addedProducts.variantProductData;

            for(let tmpIndex in tmpData) {
                let tmpEntry = tmpData[tmpIndex];

                this.saveStatusLog += 
                'Variante: <a href="' +
                    this.$router.resolve({ name: 'sw.product.detail', params: { id: tmpEntry.id, view: 'base' } }).href +
                '" target="_blank">' +
                    tmpEntry.productNumber + 
                '</a><br />';
            }

            // Speichern und Abbrechen Button ausblenden.
            this.isActionBarVisible = false;
            //this.isLoading = false;
            //this.isSaveSuccessful = true;
            //this.isSavingArticle = false;
        },

        /**
         * recalculateProcentualPrice
         */
        recalculateProcentualPrice() {
            this.$set(this.shopwareProductSettings, 'priceWarning', false);

            // Check, if the price from Craft is valid.
            let priceGros = this.myfavCraftImportArticle.craftData.retailPrice.price;

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
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

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

        /**
         * searchForSearchTerm
         */
        async searchForSearchTerm() {
            this.page = 1;
            this.loadItems();
        },

        selectConfiguration(id, item) {
            this.shopwareProductSettings.configurationId = id;
        },
        
        /**
         * selectProductBrandId
         */
        selectProductBrandId(id, item) {
            this.shopwareProductSettings.customProductBrand = id;
        },

        /**
         * selectTaxRate
         */
        selectTaxRate(id, item) {
            this.shopwareProductSettings.customTaxId = id;
        },

        /**
         * updateCustomStockValue
         */
        updateCustomStockValue(variationSku, customStockValue) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.customStockValue = customStockValue;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateProductNumber
         */
        updateProductNumber(newNumber) {
            this.shopwareProductSettings.productNumberLength = newNumber.length;
            this.checkForDoubleProductNumbers();
        },

        /**
         * updateUsePercentualDiscountForMainProduct
         */
        updateUsePercentualDiscountForMainProduct(newValue) {
            this.shopwareProductSettings.usePercentualDiscount = newValue;
            this.recalculateProcentualPrice();
        },

        /**
         * updateVariantActivated
         */
        updateVariantActivated(variationSku, activated) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.activated = activated;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateVariantArticleNumber
         */
        updateVariantArticleNumber(variationSku, newProductNumber) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.productNumber = newProductNumber;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateVariantArticleNumbers
         */
        updateVariantArticleNumbers() {
            let productNumber = 1;

            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    let skuData = variation.skus[k];
                    skuData.myfavCraftSettings.productNumber = this.shopwareProductSettings.customProductNumber + '.' + productNumber;
                    this.$set(variation.skus, k, skuData);
                    productNumber++;
                }
            }
        },

        /**
         * updateVariantArticlePrice
         * @param {*} variationSku 
         * @param {*} newPrice 
         */
        updateVariantArticlePrice(variationSku, newPrice) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.priceGros = newPrice;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateVariantArticlePrices
         */
        updateVariantArticlePrices() {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

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

        /**
         * updateVariantUseCustomPrice
         */
        updateVariantUseCustomPrice(variationSku, useCustomPrice) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.useCustomPrice = useCustomPrice;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateVariantUseCustomStock
         */
        updateVariantUseCustomStock(variationSku, useCustomStock) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.useCustomStock = useCustomStock;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * updateUpdateCustomStockNowForVariant
         */
        updateUpdateCustomStockNowForVariant(variationSku, updateCustomStockNow) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

                for(let k = 0, l = variation.skus.length; k < l; k++) {
                    if(variation.skus[k].sku === variationSku.sku) {
                        let skuData = variation.skus[k];
                        skuData.myfavCraftSettings.updateCustomStockNow = updateCustomStockNow;
                        this.$set(variation.skus, k, skuData);
                    }
                }
            }
        },

        /**
         * Event-Handler: Wenn die Checkbox "Prozentuale Preis-Berechnung" für eine Variante aktiviert/deaktiviert wurde.
         */
        updateUsePercentualDiscountForVariant(variationSku, newValue) {
            for(let i = 0, j = this.myfavCraftImportArticle.craftData.variations.length; i < j; i++) {
                let variation = this.myfavCraftImportArticle.craftData.variations[i];

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
    }
});