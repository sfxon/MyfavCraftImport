/*
 * @package inventory
 */

import template from './../detail/detail.html.twig';

const {Component, Mixin} = Shopware;
const { Criteria } = Shopware.Data;
const { mapPropertyErrors } = Shopware.Component.getComponentHelper();

Component.register('myfav-craft-verein-create', {
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
        id: {
            type: String,
            required: false,
            default: null,
        },
    },


    data() {
        return {
            // Erstelle initial eine leere categoryCollection als EntityCollection.
            // Das ist wichtig, da sonst keine Kategorien in der Kategorie-Auswahl-Liste angezeigt werden.
            categoryCollection: new Shopware.Data.EntityCollection('collection', 'collection', {}, null, []),
            isLoading: false,
            isSaveSuccessful: false,
            myfavVerein: null,
            myfavVereinIsNew: true,
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
        id() {
            this.createdComponent();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        abortOnLanguageChange() {
            return this.myfavBlogPostRepository.hasChanges(this.myfavBlogPost);
        },

        createdComponent() {
            Shopware.ExtensionAPI.publishData({
                id: 'myfav-craft-verein__myfavCraftVerein',
                path: 'myfavCraftVerein',
                scope: this,
            });
            if (this.id) {
                this.loadEntityData();
                return;
            }

            Shopware.State.commit('context/resetLanguageToDefault');
            this.myfavVerein = this.myfavVereinRepository.create();
        },

        async loadEntityData() {
            this.isLoading = true;

            const [myfavVereinResponse] = await Promise.allSettled([
                this.myfavVereinRepository.get(this.id),
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

            this.isLoading = false;
        },

        onSave() {
            this.isLoading = true;

            this.myfavVereinRepository.save(this.myfavVerein).then(() => {
                this.isLoading = false;
                this.isSaveSuccessful = true;
                if (this.id === null) {
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

        onCancel() {
            this.$router.push({ name: 'myfav.craft.verein.index' });
        },

        updateName(param1) {
            this.myfavVereinName = param1;
        }
    },
});
