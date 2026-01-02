const { ApiService } = Shopware.Classes;

/*
 * Dieser Service speichert einen Vereins-Artikel ab ->
 * bzw. sendet er ihn an den entsprechenden Endpunkt zum Speichern.
 */
export default class CraftImportedArticleSaveApiService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myfavCraftImportedArticleSave') {
        super(httpClient, loginService, apiEndpoint);
        this.name = 'CraftImportedArticleSaveApiService'; // I am not sure, what this is really for.
        this.$listener = () => ({});
    }

    /**
     * Fetch the orderDeliveryInternalState for the order given by orderId.
     */
    save(myfavVereinId, myfavCraftImportArticleId, customProductSettings, overriddenCustomProductSettings, variations) {
        const route = `/myfav/craft/imported/article/save/`

        try {
            return this.httpClient.post(
                route,
                {
                    myfavVereinId: myfavVereinId,
                    myfavCraftImportArticleId: myfavCraftImportArticleId,
                    customProductSettings: customProductSettings,
                    overriddenCustomProductSettings: overriddenCustomProductSettings,
                    variations
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