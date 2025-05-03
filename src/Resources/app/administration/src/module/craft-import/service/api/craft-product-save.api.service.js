const { ApiService } = Shopware.Classes;

export default class CraftProductSaveApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myfavCraftProductSave') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'CraftProductSaveApiService'; // I am not sure, what this is really for.
        this.$listener = () => ({});
    }

    /**
     * Fetch the orderDeliveryInternalState for the order given by orderId.
     */
    save(craftData, customProductSettings, syncProduct) {
        const route = `/myfav/craft/product/save/`

        try {
            return this.httpClient.post(
                route,
                {
                    craftData: craftData,
                    customProductSettings: customProductSettings,
                    syncProduct: syncProduct
                },
                {
                    headers: this.getBasicHeaders(),
                    responseType: 'json'
                }
            );
        } catch(e) {
            console.log(error, message);

            return {
                'status': 'error',
                'errorMessage': message
            };
        }
    }
}