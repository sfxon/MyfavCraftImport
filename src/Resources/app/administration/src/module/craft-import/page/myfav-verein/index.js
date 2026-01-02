Shopware.Module.register('myfav-craft-verein', {
    type: 'plugin',
    name: 'MyfavCraftVerein',
    title: 'myfav-craft-verein.page.list.title',
    description: 'myfav-craft-verein.page.list.description',
    color: '#F05A29',
    icon: '',

    navigation: [{
        label: 'myfav-craft-verein.page.list.menuTitle',
        color: '#F05A29',
        path: 'myfav.craft.verein.index',
        icon: '',
        parent: 'sw-catalogue',
        position: 100
    }], 

    routes: {
        index: {
            component: 'myfav-craft-verein-list',
            path: 'index'
        },
        detail: {
            component: "myfav-craft-verein-detail",
            path: 'detail/:myfavCraftVereinId',
            meta: {
                parentPath: 'myfav.craft.verein.index',
            },
            props: {
                default(route) {
                    return {
                        myfavCraftVereinId: route.params.myfavCraftVereinId,
                    };
                },
            },
        },
        create: {
            component: 'myfav-craft-verein-create',
            path: 'create',
            meta: {
                parentPath: 'myfav.craft.verein.index',
            },
        }
    },
});