<?php

namespace Fintech\Ekyc\Http\Controllers;

use Exception;
use Fintech\Core\Exceptions\DeleteOperationException;
use Fintech\Core\Exceptions\RestoreOperationException;
use Fintech\Core\Exceptions\StoreOperationException;
use Fintech\Core\Exceptions\UpdateOperationException;
use Fintech\Ekyc\Http\Requests\ImportKycStatusRequest;
use Fintech\Ekyc\Http\Requests\IndexKycStatusRequest;
use Fintech\Ekyc\Http\Requests\StoreKycStatusRequest;
use Fintech\Ekyc\Http\Requests\UpdateKycStatusRequest;
use Fintech\Ekyc\Http\Resources\KycStatusCollection;
use Fintech\Ekyc\Http\Resources\KycStatusResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Class KycStatusController
 *
 * @lrd:start
 * This class handle create, display, update, delete & restore
 * operation related to KycStatus
 *
 * @lrd:end
 */
class KycStatusController extends Controller
{
    /**
     * @lrd:start
     * Return a listing of the *KycStatus* resource as collection.
     *
     * *```paginate=false``` returns all resource as list not pagination*
     *
     * @lrd:end
     */
    public function index(IndexKycStatusRequest $request): KycStatusCollection|JsonResponse
    {
        try {
            $inputs = $request->validated();

            $kycStatusPaginate = ekyc()->kycStatus()->list($inputs);

            return new KycStatusCollection($kycStatusPaginate);

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Create a new *KycStatus* resource in storage.
     *
     * @lrd:end
     *
     * @throws StoreOperationException
     */
    public function store(StoreKycStatusRequest $request): JsonResponse
    {
        try {
            $inputs = $request->validated();

            $kycStatus = ekyc()->kycStatus()->create($inputs);

            if (! $kycStatus) {
                throw (new StoreOperationException)->setModel(config('fintech.ekyc.kyc_status_model'));
            }

            return response()->created([
                'message' => __('core::messages.resource.created', ['model' => 'Kyc Status']),
                'id' => $kycStatus->getKey(),
            ]);

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Return a specified *KycStatus* resource found by id.
     *
     * @lrd:end
     *
     * @throws ModelNotFoundException
     */
    public function show(string|int $id): KycStatusResource|JsonResponse
    {
        try {

            $kycStatus = ekyc()->kycStatus()->find($id);

            if (! $kycStatus) {
                throw (new ModelNotFoundException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            return new KycStatusResource($kycStatus);

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Update a specified *KycStatus* resource using id.
     *
     * @lrd:end
     *
     * @throws ModelNotFoundException
     * @throws UpdateOperationException
     */
    public function update(UpdateKycStatusRequest $request, string|int $id): JsonResponse
    {
        try {

            $kycStatus = ekyc()->kycStatus()->find($id);

            if (! $kycStatus) {
                throw (new ModelNotFoundException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            $inputs = $request->validated();

            if (! ekyc()->kycStatus()->update($id, $inputs)) {

                throw (new UpdateOperationException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            return response()->updated(__('core::messages.resource.updated', ['model' => 'Kyc Status']));

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Soft delete a specified *KycStatus* resource using id.
     *
     * @lrd:end
     *
     * @return JsonResponse
     *
     * @throws ModelNotFoundException
     * @throws DeleteOperationException
     */
    public function destroy(string|int $id)
    {
        try {

            $kycStatus = ekyc()->kycStatus()->find($id);

            if (! $kycStatus) {
                throw (new ModelNotFoundException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            if (! ekyc()->kycStatus()->destroy($id)) {

                throw (new DeleteOperationException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            return response()->deleted(__('core::messages.resource.deleted', ['model' => 'Kyc Status']));

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Restore the specified *KycStatus* resource from trash.
     * ** ```Soft Delete``` needs to enabled to use this feature**
     *
     * @lrd:end
     *
     * @return JsonResponse
     */
    public function restore(string|int $id)
    {
        try {

            $kycStatus = ekyc()->kycStatus()->find($id, true);

            if (! $kycStatus) {
                throw (new ModelNotFoundException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            if (! ekyc()->kycStatus()->restore($id)) {

                throw (new RestoreOperationException)->setModel(config('fintech.ekyc.kyc_status_model'), $id);
            }

            return response()->restored(__('core::messages.resource.restored', ['model' => 'Kyc Status']));

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Create a exportable list of the *KycStatus* resource as document.
     * After export job is done system will fire  export completed event
     *
     * @lrd:end
     */
    public function export(IndexKycStatusRequest $request): JsonResponse
    {
        try {
            $inputs = $request->validated();

            $kycStatusPaginate = ekyc()->kycStatus()->export($inputs);

            return response()->exported(__('core::messages.resource.exported', ['model' => 'Kyc Status']));

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }

    /**
     * @lrd:start
     * Create a exportable list of the *KycStatus* resource as document.
     * After export job is done system will fire  export completed event
     *
     * @lrd:end
     *
     * @return KycStatusCollection|JsonResponse
     */
    public function import(ImportKycStatusRequest $request): JsonResponse
    {
        try {
            $inputs = $request->validated();

            $kycStatusPaginate = ekyc()->kycStatus()->list($inputs);

            return new KycStatusCollection($kycStatusPaginate);

        } catch (Exception $exception) {

            return response()->failed($exception);
        }
    }
}
