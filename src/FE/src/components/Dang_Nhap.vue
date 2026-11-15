<template>

  <body>
    <div class="auth-card">
      <!-- Logo & Tiêu đề -->
      <div class="auth-header">
        <div class="logo-box">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1e5638" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
            <path d="M9 22v-4h6v4"></path>
            <path d="M8 6h.01"></path>
            <path d="M16 6h.01"></path>
            <path d="M12 6h.01"></path>
            <path d="M12 10h.01"></path>
            <path d="M12 14h.01"></path>
            <path d="M16 10h.01"></path>
            <path d="M16 14h.01"></path>
            <path d="M8 10h.01"></path>
            <path d="M8 14h.01"></path>
          </svg>
        </div>
        <h1 class="main-title">Đăng nhập hệ thống</h1>
      </div>
      <!-- Chuyển Tab Đăng nhập / Đăng ký -->
      <div class="tab-switcher">
        <div class="tab-btn active">Đăng nhập</div>
        <router-link to="/dang-ky">
          <div class="tab-btn">Đăng ký tài khoản mới</div>
        </router-link>
      </div>
      <!-- Form Nhập liệu -->
      <form onsubmit="return false;">
        <div class="form-group">
          <label>Email hoặc Số điện thoại</label>
          <input type="text" value="minhanh@example.com" class="form-input" />
        </div>

        <div class="form-group">
          <label>Mật khẩu</label>
          <input type="password" value="1234567890" class="form-input" />
        </div>

        <button type="submit" class="submit-btn">
          Đăng nhập
        </button>
      </form>

      <!-- Footer Link -->
      <div class="auth-footer">
        <a href="#" class="guest-link">
          🌐 Tiếp tục duyệt phòng khách sạn (Chưa đăng nhập) →
        </a>
      </div>
    </div>

  </body>
</template>
<script>
export default {

}
</script>
<style>
/* Nền tổng thể màu kem nhạt giống mẫu */
body {
  margin: 0;
  padding: 0;
  min-height: 90vh;
  background-color: #f6f5f0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1f2937;
}

/* Khung thẻ chính */
.auth-card {
  width: 100%;
  max-width: 480px;
  background: #ffffff;
  border-radius: 16px;
  padding: 28px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
  border: 1px solid #eceae2;
  box-sizing: border-box;
}

/* Header */
.auth-header {
  text-align: center;
  margin-bottom: 24px;
}

.logo-box {
  width: 44px;
  height: 44px;
  background-color: #eaf3ee;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
}

.subtitle-top {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.08em;
  color: #6b7280;
  margin: 0 0 6px;
  text-transform: uppercase;
}

.main-title {
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 6px;
}

.subtitle-desc {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
}

/* Chọn vai trò */
.role-section {
  margin-bottom: 16px;
}

.section-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  color: #4b5563;
  margin-bottom: 8px;
  letter-spacing: 0.03em;
}

.role-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  background-color: #f3f1ec;
  padding: 6px;
  border-radius: 12px;
}

.role-card {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 8px;
  padding: 10px 12px;
  text-align: left;
  cursor: pointer;
  transition: all 0.2s ease;
}

.role-card.active {
  background: #ffffff;
  border-color: #1e5638;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.role-title {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 2px;
}

.role-card:not(.active) .role-title {
  color: #6b7280;
}

.role-desc {
  font-size: 11px;
  color: #6b7280;
}

/* Tab Đăng nhập / Đăng ký */
.tab-switcher {
  display: grid;
  grid-template-columns: 1fr 1fr;
  background-color: #f3f1ec;
  padding: 4px;
  border-radius: 10px;
  margin-bottom: 16px;
}

.tab-btn {
  padding: 8px;
  border: none;
  background: transparent;
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
}

.tab-btn.active {
  background: #ffffff;
  color: #111827;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

/* Box 1-Click Demo */
.demo-box {
  background-color: #faf9f6;
  border: 1px solid #ebe8df;
  border-radius: 12px;
  padding: 12px;
  margin-bottom: 18px;
}

.demo-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 10px;
}

.demo-badge {
  color: #1e5638;
  font-size: 11px;
  font-weight: 700;
}

.demo-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}

.demo-item {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  padding: 8px 10px;
  text-align: left;
  cursor: pointer;
  transition: border-color 0.2s;
}

.demo-item:hover {
  border-color: #1e5638;
}

.demo-name {
  font-size: 12.5px;
  font-weight: 700;
  color: #111827;
  margin-bottom: 2px;
}

.demo-sub {
  font-size: 11px;
  color: #6b7280;
}

/* Form */
.form-group {
  margin-bottom: 14px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 6px;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  font-size: 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
  color: #111827;
  background-color: #ffffff;
}

.form-input:focus {
  border-color: #1e5638;
  box-shadow: 0 0 0 3px rgba(30, 86, 56, 0.1);
}

/* Nút Submit xanh rêu đậm */
.submit-btn {
  width: 100%;
  background-color: #1e5638;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 12px 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 6px;
  transition: background-color 0.2s;
}

.submit-btn:hover {
  background-color: #17442c;
}

/* Footer */
.auth-footer {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #f3f4f6;
  text-align: center;
}

.guest-link {
  font-size: 13px;
  font-weight: 600;
  color: #1e5638;
  text-decoration: none;
}

.guest-link:hover {
  text-decoration: underline;
}
</style>
