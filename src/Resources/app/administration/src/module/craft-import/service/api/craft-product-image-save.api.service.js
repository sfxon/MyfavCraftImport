const { ApiService } = Shopware.Classes;

export default class CraftProductImageSaveApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myfavCraftProductImageSave') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'CraftProductImageSaveApiService'; // I am not sure, what this is really for.
        this.$listener = () => ({});
    }

    /**
     * Fetch the orderDeliveryInternalState for the order given by orderId.
     */
    save(productId, productNumber, imageUrls) {
        const route = `/myfav/craft/product/image/save/`;

        return this.httpClient.post(
            route,
            {
                productId: productId,
                productNumber: productNumber,
                imageUrls: imageUrls,
            },
            {
                headers: this.getBasicHeaders(),
                responseType: 'json'
            }
        );
    }
}