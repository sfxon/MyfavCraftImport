import './page/myfav-craft-import-dashboard';
import './page/myfav-craft-import-article';

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