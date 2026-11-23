<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dich_VuRequest;
use App\Models\Dich_Vu;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Dich_VuController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lấy danh sách dịch vụ kèm tìm kiếm, lọc và phân trang (UC17).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $vaiTro = $user->vaiTro;

        // Chỉ Quản lý, Lễ tân và Khách hàng được phép truy cập
        if (!in_array($vaiTro, ['Quản lý', 'Admin', 'Lễ tân', 'Khách hàng'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập danh sách dịch vụ.', 403);
        }

        $query = Dich_Vu::query();

        // BR04.5 & Requirement 11: Khách hàng và Lễ tân chỉ được xem dịch vụ 'Đang cung cấp'
        if (!in_array($vaiTro, ['Quản lý', 'Admin'], true)) {
            $query->where('trangThai', 'Đang cung cấp');
        } else {
            // Quản lý có thể lọc theo trạng thái nếu có chỉ định
            if ($request->filled('trangThai')) {
                $query->where('trangThai', $request->input('trangThai'));
            }
        }

        // Tìm kiếm theo tên dịch vụ hoặc từ khóa
        $keyword = trim($request->input('keyword') ?? $request->input('q') ?? '');
        if ($keyword !== '') {
            $query->where(function ($sub) use ($keyword) {
                $sub->where('tenDichVu', 'like', "%{$keyword}%")
                    ->orWhere('moTa', 'like', "%{$keyword}%");
            });
        }

        $query->orderBy('id', 'asc');

        // Phân trang nếu có tham số 'page' hoặc 'per_page'
        if ($request->filled('page')) {
            $perPage = (int) $request->input('per_page', 15);
            $paginated = $query->paginate($perPage);

            return $this->successResponse($paginated->items(), 'Lấy danh sách dịch vụ thành công.', 200, [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ]);
        }

        $services = $query->get();

        return $this->successResponse($services, 'Lấy danh sách dịch vụ thành công.', 200);
    }

    /**
     * Xem thông tin chi tiết một dịch vụ theo ID.
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $vaiTro = $user->vaiTro;

        // Chỉ Quản lý, Lễ tân và Khách hàng được phép xem chi tiết
        if (!in_array($vaiTro, ['Quản lý', 'Admin', 'Lễ tân', 'Khách hàng'], true)) {
            return $this->errorResponse('Bạn không có quyền truy cập thông tin dịch vụ.', 403);
        }

        $service = Dich_Vu::find($id);

        if (!$service) {
            return $this->errorResponse('Không tìm thấy dịch vụ yêu cầu.', 404);
        }

        // Nếu không phải Quản lý, không cho phép xem chi tiết dịch vụ đã ngừng cung cấp
        if (!in_array($vaiTro, ['Quản lý', 'Admin'], true) && $service->trangThai !== 'Đang cung cấp') {
            return $this->errorResponse('Dịch vụ này đã ngừng cung cấp.', 404);
        }

        return $this->successResponse($service, 'Lấy thông tin chi tiết dịch vụ thành công.', 200);
    }

    /**
     * Thêm mới một dịch vụ vào danh mục (Chỉ Quản lý).
     *
     * @param Dich_VuRequest $request
     * @return JsonResponse
     */
    public function store(Dich_VuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $service = Dich_Vu::create([
                'tenDichVu' => $data['tenDichVu'],
                'moTa'      => $data['moTa'],
                'donGia'    => (int) $data['donGia'],
                'trangThai' => $data['trangThai'],
            ]);

            DB::commit();

            return $this->successResponse($service, 'Thêm dịch vụ mới thành công.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Lỗi khi thêm dịch vụ mới: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi thêm dịch vụ.', 500);
        }
    }

    /**
     * Cập nhật thông tin dịch vụ (Chỉ Quản lý).
     * Bảo toàn BR04.5: Không cập nhật giá áp dụng của các yêu cầu dịch vụ đã phát sinh trước đó.
     *
     * @param int|string $id
     * @param Dich_VuRequest $request
     * @return JsonResponse
     */
    public function update($id, Dich_VuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $service = Dich_Vu::lockForUpdate()->find($id);

            if (!$service) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy dịch vụ cần cập nhật.', 404);
            }

            $data = $request->validated();

            // Cập nhật thông tin danh mục dịch vụ mới
            $service->update([
                'tenDichVu' => $data['tenDichVu'],
                'moTa'      => $data['moTa'],
                'donGia'    => (int) $data['donGia'],
                'trangThai' => $data['trangThai'],
            ]);

            DB::commit();

            return $this->successResponse($service->fresh(), 'Cập nhật thông tin dịch vụ thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật dịch vụ #{$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi cập nhật dịch vụ.', 500);
        }
    }

    /**
     * Cập nhật nhanh trạng thái dịch vụ (Đang cung cấp / Ngừng cung cấp).
     *
     * @param int|string $id
     * @param Dich_VuRequest $request
     * @return JsonResponse
     */
    public function updateStatus($id, Dich_VuRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $service = Dich_Vu::lockForUpdate()->find($id);

            if (!$service) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy dịch vụ cần cập nhật trạng thái.', 404);
            }

            $trangThai = $request->input('trangThai');
            $service->update([
                'trangThai' => $trangThai,
            ]);

            DB::commit();

            return $this->successResponse($service->fresh(), 'Cập nhật trạng thái dịch vụ thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Lỗi khi cập nhật trạng thái dịch vụ #{$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi cập nhật trạng thái dịch vụ.', 500);
        }
    }

    /**
     * Xóa dịch vụ hoặc chuyển sang ngừng cung cấp.
     * Chặn xóa cứng nếu dịch vụ đã phát sinh giao dịch lịch sử (BR04.5).
     *
     * @param int|string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($id, Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->errorResponse('Chưa đăng nhập.', 401);
        }

        $vaiTro = $user->vaiTro;
        if (!in_array($vaiTro, ['Quản lý', 'Admin'], true)) {
            return $this->errorResponse('Bạn không có quyền xóa dịch vụ.', 403);
        }

        DB::beginTransaction();
        try {
            $service = Dich_Vu::lockForUpdate()->find($id);

            if (!$service) {
                DB::rollBack();
                return $this->errorResponse('Không tìm thấy dịch vụ cần xóa.', 404);
            }

            // Kiểm tra dịch vụ đã phát sinh yêu cầu sử dụng trong yeu_cau_dich_vu chưa
            if ($service->yeuCauDichVus()->exists()) {
                DB::rollBack();
                return $this->errorResponse(
                    'Dịch vụ đã từng phát sinh yêu cầu sử dụng từ khách hàng, không thể xóa cứng. Vui lòng chuyển trạng thái sang "Ngừng cung cấp" để bảo toàn dữ liệu kế toán (BR04.5).',
                    409
                );
            }

            $service->delete();

            DB::commit();

            return $this->successResponse(null, 'Xóa dịch vụ thành công.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Lỗi khi xóa dịch vụ #{$id}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse('Đã xảy ra lỗi hệ thống khi xóa dịch vụ.', 500);
        }
    }
}
