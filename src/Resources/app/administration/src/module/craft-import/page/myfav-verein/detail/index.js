/*
 * @package inventory
 */

import template from './detail.html.twig';
import './detail.scss';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('myfav-craft-verein-detail', {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('placeholder'),
        Mixin.getByName('notification'),
    ],

    shortcuts: {
        'SYSTEMKEY+S': 'onSave',
        ESCAPE: 'onCancel',
    },

    props: {
        myfavCraftVereinId: {
            type: String,
            required: true,
            default: null,
        },
    },


    data() {
        return {
            // Erstelle initial eine leere categoryCollection als EntityCollection.
            // Das ist wichtig, da sonst keine Kategorien in der Kategorie-Auswahl-Liste angezeigt werden.
            categoryCollection: new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []),
            myfavVerein: null,
            isLoading: false,
            isSaveSuccessful: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(this.identifier),
        };
    },

    computed: {
        identifier() {
            return this.placeholder(this.myfavVerein, 'name');
        },

        categoryCriteria() {
            const criteria = new Criteria(1, 500);
            return criteria;
        },

        categoryRepository() {
            return this.repositoryFactory.create('category');
        },

        myfavVereinIsLoading() {
            return this.isLoading || this.myfavVerein == null;
        },

        myfavVereinRepository() {
            return this.repositoryFactory.create('myfav_verein');
        },

        ...mapPropertyErrors('myfavVerein', ['name']),
    },

    watch: {
        myfavCraftVereinId() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        abortOnLanguageChange() {
            return this.myfavVereinRepository.hasChanges(this.myfavVerein);
        },

        // Prüft, ob das aktuelle Artikelnummer Token schon an einen anderen Verein vergeben ist.
        async checkToken() {
            let productNumberToken = this.myfavVerein.productNumberToken;
            let vereinId = this.myfavVerein.id;
            const criteria = new Shopware.Data.Criteria();

            criteria.addFilter(
                Criteria.equals('productNumberToken', productNumberToken)
            );

            // UND: ID ist NICHT die aktuelle ID
            criteria.addFilter(
                Criteria.not('AND', [Criteria.equals('id', vereinId)])
            );

            const [tmpVereinResponse] = await Promise.allSettled([
                this.myfavVereinRepository.search(
                    criteria,
                    Shopware.Context.api,
                )
            ]);

            console.log(tmpVereinResponse);

            if (tmpVereinResponse.value !== null && tmpVereinResponse.value.total > 0) {
                // Es gibt bereits einen Eintrag mit diesem Token
                return false;
            }

            return true;
        },

        createdComponent() {
            Shopware.ExtensionAPI.publishData({
                id: 'myfav-craft-verein__myfavCraftVerein',
                path: 'myfavCraftVerein',
                scope: this,
            });

            if (this.myfavCraftVereinId) {
                this.loadEntityData();
                return;
            }

            this.myfavVerein = this.myfavVereinRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            // Verein laden.
            const [myfavVereinResponse] = await Promise.allSettled([
                this.myfavVereinRepository.get(
                    this.myfavCraftVereinId,
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

            this.isLoading = false;
        },

        onCancel() {
            this.$router.push({ name: 'myfav.craft.verein.index' });
        },


        async onSave() {
            this.isLoading = true;

            // CategoryId zuweisen.
            // Wir nehmen bei Mehrfachauswahl immer nur die erste.
            let category = this.categoryCollection.first();
            let categoryId = null;

            if(category !== null) {
                categoryId = category.id;
            }

            this.myfavVerein.categoryId = categoryId;

            // Prüfe, dass das Token noch nicht vergeben ist.
            let tokenCheckResult = await this.checkToken();

            if(tokenCheckResult === false) {
                this.createNotificationError({
                    message: "Dieses Artikel-Nummer-Token ist schon vergeben.",
                });
                this.isLoading = false;
                return;
            }

            // Speichervorgang: Daten an Server senden.
            this.myfavVereinRepository.save(this.myfavVerein).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;

                if (this.myfavCraftVereinId === null) {
                    this.$router.push({ name: 'myfav.craft.verein.index' });
                    return;
                }

                this.loadEntityData();
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    message: this.$tc(
                        'global.notification.notificationSaveErrorMessageRequiredFieldsInvalid',
                    ),
                });
                throw exception;
            });
        },
    },
});
