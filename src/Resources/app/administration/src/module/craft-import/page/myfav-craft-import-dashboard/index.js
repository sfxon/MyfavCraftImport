import template from './dashboard.html.twig';
import './dashboard.scss';

const {Component, Mixin} = Shopware;

Component.register('myfav-craft-import-dashboard', {
    template,

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
});
