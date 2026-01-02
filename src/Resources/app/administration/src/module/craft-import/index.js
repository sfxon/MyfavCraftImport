import './page/myfav-craft-import-dashboard';
import './page/myfav-craft-import-article';
import './page/myfav-craft-import-select-verein';
import './page/myfav-craft-imported-article';
import './page/myfav-craft-import-assign-verein';

/* Admin Pages for Blog Posts */
import './page/myfav-verein';
import './page/myfav-verein/list';
import './page/myfav-verein/detail';
import './page/myfav-verein/create';

import deDE from './snippet/de-DE';
import enGB from './snippet/en-GB';

const { Module } = Shopware;

Module.register('myfav-craft-import', {
    type: 'plugin',
    name: 'Craft-Import',
    title: 'myfav-craft-import.general.mainMenuItemGeneral',
    description: 'myfav-craft-import.general.descriptionTextModule',
    color: '#ff3d58',
    icon: 'regular-shopping-bag',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        article: {
            component: 'myfav-craft-import-article',
            path: 'article'
        },
        dashboard: {
            component: 'myfav-craft-import-dashboard',
            path: 'dashboard'
        },
        importedArticle: {
            component: 'myfav-craft-imported-article',
            path: 'imported-article'
        },
        selectVerein: {
            component: 'myfav-craft-import-select-verein',
            path: 'select-verein/:myfavCraftImportArticleId',
            props: {
                default(route) {
                    return {
                        myfavCraftImportArticleId: route.params.myfavCraftImportArticleId,
                    };
                },
            },
        },
        assignVerein: {
            component: 'myfav-craft-import-assign-verein',
            path: 'assign-verein/:myfavCraftImportArticleId/:myfavVereinId',
            props: {
                default(route) {
                    return {
                        myfavCraftImportArticleId: route.params.myfavCraftImportArticleId,
                        myfavVereinId: route.params.myfavVereinId,
                    };
                },
            },
        }
    },

    navigation: [{
        label: 'Craft-Import',
        color: '#ff3d58',
        path: 'myfav.craft.import.dashboard',
        icon: 'default-shopping-paper-bag-product',
        parent: 'sw-catalogue',
        position: 1000,
        privilege: 'myfav_craft_import.viewer'
    }]
});