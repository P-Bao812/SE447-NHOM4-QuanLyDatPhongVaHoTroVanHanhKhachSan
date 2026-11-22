<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thong_Bao;
use Carbon\Carbon;

class Thong_BaoController extends Controller
{
    /**
     * Lấy danh sách thông báo theo vai trò hoặc bộ phận
     */
    public function index(Request $request)
    {
        try {
            $vaiTro = $request->header('X-User-Role')
                ?? $request->input('userRole')
                ?? $request->query('userRole')
                ?? $request->query('boPhan')
                ?? 'Admin';

            $maNhanVien = $request->header('X-User-Id')
                ?? $request->query('maNhanVien')
                ?? $request->input('maNhanVien');

            $query = Thong_Bao::with(['yeuCauDichVu', 'phieuBuongPhong'])
                ->orderBy('id', 'desc');

            // Phân quyền thông báo theo vai trò / bộ phận / nhân viên cụ thể
            if (!in_array($vaiTro, ['Admin', 'admin', 'Chủ khách sạn', 'all'])) {
                $query->where(function ($q) use ($vaiTro, $maNhanVien) {
                    $q->where('vaiTroNhan', $vaiTro)
                      ->orWhere('vaiTroNhan', 'like', "%{$vaiTro}%")
                      ->orWhereNull('vaiTroNhan')
                      ->orWhere('vaiTroNhan', 'Tat_Ca');

                    if ($maNhanVien) {
                        $q->orWhere('maNhanVienNhan', $maNhanVien);
                    }

                    if (str_contains(mb_strtolower($vaiTro), 'buồng phòng')) {
                        $q->orWhere('vaiTroNhan', 'Buồng phòng');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'lễ tân')) {
                        $q->orWhere('vaiTroNhan', 'Lễ tân');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'nhà hàng') || str_contains(mb_strtolower($vaiTro), 'bếp') || str_contains(mb_strtolower($vaiTro), 'phục vụ')) {
                        $q->orWhere('vaiTroNhan', 'Nhà hàng & Bếp');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'spa')) {
                        $q->orWhere('vaiTroNhan', 'Spa & Massage');
                    }
                });
            }

            if ($request->has('chuaXem') && $request->chuaXem == 1) {
                $query->where('trangThai', 'Chua_Xem');
            }

            $notifications = $query->take(30)->get();

            $unreadCount = Thong_Bao::where(function ($q) use ($vaiTro, $maNhanVien) {
                if (!in_array($vaiTro, ['Admin', 'admin', 'Chủ khách sạn', 'all'])) {
                    $q->where('vaiTroNhan', $vaiTro)
                      ->orWhere('vaiTroNhan', 'like', "%{$vaiTro}%")
                      ->orWhereNull('vaiTroNhan')
                      ->orWhere('vaiTroNhan', 'Tat_Ca');

                    if ($maNhanVien) {
                        $q->orWhere('maNhanVienNhan', $maNhanVien);
                    }

                    if (str_contains(mb_strtolower($vaiTro), 'buồng phòng')) {
                        $q->orWhere('vaiTroNhan', 'Buồng phòng');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'lễ tân')) {
                        $q->orWhere('vaiTroNhan', 'Lễ tân');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'nhà hàng') || str_contains(mb_strtolower($vaiTro), 'bếp') || str_contains(mb_strtolower($vaiTro), 'phục vụ')) {
                        $q->orWhere('vaiTroNhan', 'Nhà hàng & Bếp');
                    }
                    if (str_contains(mb_strtolower($vaiTro), 'spa')) {
                        $q->orWhere('vaiTroNhan', 'Spa & Massage');
                    }
                }
            })->where('trangThai', 'Chua_Xem')->count();

            $data = $notifications->map(function ($item) {
                return [
                    'id' => $item->id,
                    'loai' => $item->loai,
                    'tieuDe' => $item->tieuDe,
                    'noiDung' => $item->noiDung,
                    'maYeuCauDichVu' => $item->maYeuCauDichVu,
                    'maPhieuBuongPhong' => $item->maPhieuBuongPhong,
                    'vaiTroNhan' => $item->vaiTroNhan,
                    'trangThai' => $item->trangThai,
                    'thoiGian' => $item->created_at ? $item->created_at->diffForHumans() : 'Vừa xong',
                    'ngayTao' => $item->created_at ? $item->created_at->format('H:i d/m/Y') : ''
                ];
            });

            return response()->json([
                'status' => 'success',
                'unreadCount' => $unreadCount,
                'total' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải danh sách thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đánh dấu 1 thông báo đã xem
     */
    public function markAsRead($id)
    {
        try {
            $tb = Thong_Bao::find($id);
            if ($tb) {
                $tb->trangThai = 'Da_Xem';
                $tb->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Đã đánh dấu thông báo là đã xem.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả thông báo là đã xem
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $vaiTro = $request->header('X-User-Role')
                ?? $request->input('userRole')
                ?? 'Admin';

            $query = Thong_Bao::where('trangThai', 'Chua_Xem');
            if (!in_array($vaiTro, ['Admin', 'admin', 'Chủ khách sạn'])) {
                $query->where(function ($q) use ($vaiTro) {
                    $q->where('vaiTroNhan', $vaiTro)
                      ->orWhereNull('vaiTroNhan');
                });
            }

            $query->update(['trangThai' => 'Da_Xem']);

            return response()->json([
                'status' => 'success',
                'message' => 'Đã đánh dấu toàn bộ thông báo là đã xem.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi cập nhật tất cả thông báo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper tĩnh để tạo thông báo nhanh từ các controller khác
     */
    public static function guiThongBao($loai, $tieuDe, $noiDung, $maYeuCauDichVu = null, $vaiTroNhan = null, $maPhieuBuongPhong = null, $maNhanVienNhan = null)
    {
        try {
            return Thong_Bao::create([
                'loai' => $loai,
                'tieuDe' => $tieuDe,
                'noiDung' => $noiDung,
                'maYeuCauDichVu' => $maYeuCauDichVu,
                'maPhieuBuongPhong' => $maPhieuBuongPhong,
                'vaiTroNhan' => $vaiTroNhan,
                'maNhanVienNhan' => $maNhanVienNhan,
                'trangThai' => 'Chua_Xem'
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi tạo thông báo: ' . $e->getMessage());
            return null;
        }
    }
}
