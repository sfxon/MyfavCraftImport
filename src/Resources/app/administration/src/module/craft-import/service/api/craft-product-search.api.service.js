const { ApiService } = Shopware.Classes;

export default class CraftProductSearchApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myfavCraftProductSearch') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'CraftProductSearchApiService'; // I am not sure, what this is really for.
        this.$listener = () => ({});
    }

    /**
     * Fetch the orderDeliveryInternalState for the order given by orderId.
     */
    search(productNumber) {
        const route = `/myfav/craft/product/search/${productNumber}`;

        return this.httpClient.get(route, {
            headers: this.getBasicHeaders(),
            responseType: 'json'
        });
    }
}
