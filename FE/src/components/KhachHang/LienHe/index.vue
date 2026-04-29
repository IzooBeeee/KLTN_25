<template>
    <div class="directory-container">
        <!-- Header -->
        <div class="directory-header">
            <h2 class="page-title">Danh bạ Môi giới</h2>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input v-model="searchQuery" @input="debouncedSearch" type="text"
                    placeholder="Tìm theo tên, số điện thoại hoặc khu vực..." />
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-state">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>

        <!-- Danh sách -->
        <div v-else class="broker-list">
            <div v-for="broker in brokers" :key="broker.id" class="broker-card">
                <!-- 1. Avatar -->
                <div class="broker-avatar">
                    <img :src="broker.avatar || getDefaultAvatar(broker.ho_ten)" :alt="broker.ho_ten" loading="lazy" />
                </div>

                <!-- 2. Thông tin liên hệ -->
                <div class="broker-contact">
                    <h3 class="broker-name">{{ broker.ho_ten }}</h3>

                    <!-- Thay địa chỉ bằng mô tả -->
                    <div class="contact-item description-text" v-if="broker.mo_ta">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ broker.mo_ta }}</span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>{{ broker.dia_chi }}</span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-phone-fill"></i>
                        <a :href="`tel:${broker.so_dien_thoai}`" class="phone-link">
                            {{ formatPhone(broker.so_dien_thoai) }}
                        </a>
                    </div>

                    <!-- Nút Gửi Email -->
                    <button class="btn-email" @click="openContactModal(broker)">
                        <i class="bi bi-envelope"></i> Gửi Email
                    </button>
                </div>

                <!-- 3. Khu vực hoạt động (từ bài đăng) -->
                <div class="broker-specialties">
                    <h4 class="specialties-title">KHU VỰC CÁ NHÂN MÔI GIỚI</h4>
                    <ul class="specialties-list">
                        <li v-for="(baiDang, idx) in broker.bai_dang" :key="idx">
                            {{ baiDang.tieu_de }}<span v-if="baiDang.dia_chi">, {{ baiDang.dia_chi }}</span>
                        </li>
                        <li v-if="!broker.bai_dang || broker.bai_dang.length === 0" class="no-data">
                            Chưa có bài đăng
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="brokers.length === 0 && !loading" class="empty-state">
                <i class="bi bi-search"></i>
                <p>Không tìm thấy môi giới phù hợp</p>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > pagination.per_page" class="pagination-wrapper">
                <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
                    class="btn-page">
                    ← Trước
                </button>
                <span class="page-info">
                    Trang {{ pagination.current_page }} / {{ Math.ceil(pagination.total / pagination.per_page) }}
                </span>
                <button @click="changePage(pagination.current_page + 1)"
                    :disabled="pagination.current_page * pagination.per_page >= pagination.total" class="btn-page">
                    Sau →
                </button>
            </div>
        </div>

        <!-- ✅ MODAL LIÊN HỆ (Gộp chung ở đây) -->
        <transition name="fade">
            <div v-if="showContactModal" class="modal-overlay" @click="closeContactModal"></div>
        </transition>

        <transition name="modal-slide">
            <div v-if="showContactModal" class="modal-container">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h3 class="modal-title">Liên hệ với {{ selectedBroker?.ho_ten }}</h3>
                    <button class="close-btn" @click="closeContactModal" aria-label="Đóng">✕</button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <p class="modal-desc">
                        Vui lòng xác nhận các thông tin chi tiết sau để chúng tôi phục vụ bạn tốt nhất
                    </p>

                    <form @submit.prevent="submitContactForm" class="contact-form">
                        <!-- Tiêu đề -->
                        <div class="form-group">
                            <label class="form-label">Tiêu đề <span class="required">*</span></label>
                            <input v-model="contactForm.subject" type="text" class="form-input" placeholder="Tiêu đề"
                                :class="{ 'input-error': contactErrors.subject }"
                                @input="clearContactError('subject')" />
                            <span v-if="contactErrors.subject" class="error-text">{{ contactErrors.subject }}</span>
                        </div>

                        <!-- Tên liên hệ -->
                        <div class="form-group">
                            <label class="form-label">Tên liên hệ <span class="required">*</span></label>
                            <input v-model="contactForm.customer_name" type="text" class="form-input"
                                placeholder="Họ và tên của bạn" :class="{ 'input-error': contactErrors.customer_name }"
                                @input="clearContactError('customer_name')" />
                            <span v-if="contactErrors.customer_name" class="error-text">{{ contactErrors.customer_name
                                }}</span>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label class="form-label">Email <span class="required">*</span></label>
                            <input v-model="contactForm.customer_email" type="email" class="form-input"
                                placeholder="email@example.com" :class="{ 'input-error': contactErrors.customer_email }"
                                @input="clearContactError('customer_email')" />
                            <span v-if="contactErrors.customer_email" class="error-text">{{ contactErrors.customer_email
                                }}</span>
                        </div>

                        <!-- Nội dung -->
                        <div class="form-group">
                            <label class="form-label">Nội dung <span class="required">*</span></label>
                            <textarea v-model="contactForm.content" class="form-textarea" rows="5"
                                placeholder="Nội dung" :class="{ 'input-error': contactErrors.content }"
                                @input="clearContactError('content')"></textarea>
                            <div class="char-count">{{ contactForm.content.length }}/2000 kí tự</div>
                            <span v-if="contactErrors.content" class="error-text">{{ contactErrors.content }}</span>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" @click="closeContactModal"
                                :disabled="contactLoading">
                                Hủy
                            </button>
                            <button type="submit" class="btn-submit" :disabled="contactLoading">
                                <span v-if="contactLoading" class="spinner"></span>
                                <span v-else>Gửi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
import api from '@/axios/config';

export default {
    name: "BrokerDirectory",
    data() {
        return {
            // ===== Data chính =====
            brokers: [],
            loading: false,
            searchQuery: "",
            searchTimeout: null,
            pagination: { current_page: 1, total: 0, per_page: 20 },
            currentUser: { name: '', email: '' },

            // ===== Data Modal liên hệ =====
            showContactModal: false,
            contactLoading: false,
            selectedBroker: null,
            contactForm: {
                subject: '',
                customer_name: '',
                customer_email: '',
                content: ''
            },
            contactErrors: {}
        };
    },
    mounted() {
        this.fetchBrokers();
        this.loadCurrentUser();
    },
    methods: {
        // ===== Methods chính =====
        loadCurrentUser() {
            const user = localStorage.getItem('user_info');
            if (user) {
                const userData = JSON.parse(user);
                this.currentUser = {
                    name: userData.ten || userData.name || '',
                    email: userData.email || ''
                };
            }
        },

        async fetchBrokers(page = 1, search = '') {
            this.loading = true;
            try {
                const params = { page };
                if (search) params.search = search;
                const response = await api.get('/moi-gioi/danh-ba', { params });
                if (response.data.status) {
                    this.brokers = response.data.data;
                    this.pagination = response.data.pagination;
                }
            } catch (error) {
                console.error("Lỗi tải danh sách:", error);
            } finally {
                this.loading = false;
            }
        },

        debouncedSearch() {
            if (this.searchTimeout) clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchBrokers(1, this.searchQuery);
            }, 500);
        },

        changePage(page) {
            if (page < 1) return;
            this.fetchBrokers(page, this.searchQuery);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        formatPhone(phone) {
            if (!phone) return '';
            return phone.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
        },

        getDefaultAvatar(name) {
            const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            const colors = ['#0891b2', '#10b981', '#f59e0b', '#ef4444'];
            const color = colors[name.length % colors.length];
            return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${color.replace('#', '')}&color=fff&size=200`;
        },

        // ===== Methods Modal liên hệ =====
        openContactModal(broker) {
            this.selectedBroker = broker;
            this.contactForm = {
                subject: '',
                customer_name: this.currentUser.name || '',
                customer_email: this.currentUser.email || '',
                content: ''
            };
            this.contactErrors = {};
            this.showContactModal = true;
            document.body.style.overflow = 'hidden';
        },

        closeContactModal() {
            this.showContactModal = false;
            document.body.style.overflow = '';
            setTimeout(() => {
                this.contactErrors = {};
                this.contactForm = { subject: '', customer_name: '', customer_email: '', content: '' };
            }, 300);
        },

        clearContactError(field) {
            if (this.contactErrors[field]) delete this.contactErrors[field];
        },

        validateContactForm() {
            this.contactErrors = {};
            let isValid = true;

            if (!this.contactForm.subject.trim()) {
                this.contactErrors.subject = 'Vui lòng nhập tiêu đề';
                isValid = false;
            }
            if (!this.contactForm.customer_name.trim()) {
                this.contactErrors.customer_name = 'Vui lòng nhập tên liên hệ';
                isValid = false;
            }
            if (!this.contactForm.customer_email.trim()) {
                this.contactErrors.customer_email = 'Vui lòng nhập email';
                isValid = false;
            } else if (!this.isValidEmail(this.contactForm.customer_email)) {
                this.contactErrors.customer_email = 'Email không hợp lệ';
                isValid = false;
            }
            if (!this.contactForm.content.trim()) {
                this.contactErrors.content = 'Vui lòng nhập nội dung';
                isValid = false;
            } else if (this.contactForm.content.length > 2000) {
                this.contactErrors.content = 'Nội dung không được vượt quá 2000 ký tự';
                isValid = false;
            }
            return isValid;
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        async submitContactForm() {
            if (!this.validateContactForm()) return;
            this.contactLoading = true;

            try {
                const response = await api.post(
                    `/moi-gioi/${this.selectedBroker.id}/contact`,
                    {
                        broker_id: this.selectedBroker.id,
                        ...this.contactForm
                    }
                );

                if (response.data.status) {
                    if (this.$swal) {
                        this.$swal({
                            icon: 'success',
                            title: 'Gửi thành công!',
                            text: 'Email đã được gửi đến môi giới.',
                            confirmButtonText: 'Đóng',
                            confirmButtonColor: '#10b981',
                        });
                    } else {
                        alert('Gửi thành công!');
                    }
                    this.closeContactModal();
                }
            } catch (error) {
                const message = error.response?.data?.message || 'Có lỗi xảy ra. Vui lòng thử lại.';
                if (this.$swal) {
                    this.$swal({
                        icon: 'error',
                        title: 'Gửi thất bại',
                        text: message,
                        confirmButtonText: 'Thử lại',
                        confirmButtonColor: '#ef4444',
                    });
                } else {
                    alert(message);
                }
            } finally {
                this.contactLoading = false;
            }
        }
    },
    beforeUnmount() {
        document.body.style.overflow = '';
    }
};
</script>

<style scoped>
/* ===== Styles chính (giữ nguyên) ===== */
.directory-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.directory-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 450px;
}

.search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 1.1rem;
}

.search-box input {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
    background: white;
}

.search-box input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

.broker-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.broker-card {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.broker-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
    transform: translateY(-3px);
}

.broker-avatar img {
    width: 110px;
    height: 130px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid #f3f4f6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.broker-contact {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
}

.broker-name {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.5rem 0;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    color: #6b7280;
    font-size: 0.95rem;
    line-height: 1.5;
}

.contact-item i {
    color: #10b981;
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
}

.description-text {
    font-style: italic;
    color: #4b5563;
    background: #f9fafb;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
    border-left: 3px solid #10b981;
}

.phone-link {
    color: #111827;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.phone-link:hover {
    color: #10b981;
}

.btn-email {
    margin-top: 0.75rem;
    padding: 0.6rem 1.25rem;
    background: white;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    width: fit-content;
    font-size: 0.9rem;
}

.btn-email:hover {
    background: #f0fdf4;
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-1px);
}

.broker-specialties {
    width: 520px;
    border-left: 2px solid #e5e7eb;
    padding-left: 1.5rem;
}

.specialties-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1rem 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.specialties-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.specialties-list li {
    position: relative;
    padding-left: 1.3rem;
    margin-bottom: 0.7rem;
    font-size: 0.9rem;
    color: #4b5563;
    line-height: 1.6;
}

.specialties-list li::before {
    content: "•";
    position: absolute;
    left: 0;
    color: #ef4444;
    font-weight: bold;
    font-size: 1.3rem;
    line-height: 1.2;
}

.specialties-list li.no-data {
    color: #9ca3af;
    font-style: italic;
}

.loading-state {
    text-align: center;
    padding: 4rem;
}

.empty-state {
    text-align: center;
    padding: 4rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    margin-top: 2.5rem;
    padding: 1.5rem;
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.btn-page {
    padding: 0.6rem 1.75rem;
    background: white;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    color: #374151;
}

.btn-page:hover:not(:disabled) {
    background: #f0fdf4;
    border-color: #10b981;
    color: #10b981;
    transform: translateY(-1px);
}

.btn-page:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.page-info {
    color: #6b7280;
    font-size: 0.95rem;
    font-weight: 500;
}

/* ===== Modal Styles ===== */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 9998;
}

.modal-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    z-index: 9999;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid #f3f4f6;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    transition: all 0.2s;
}

.close-btn:hover {
    background: #f3f4f6;
    color: #111827;
}

.modal-body {
    padding: 24px;
}

.modal-desc {
    margin: 0 0 24px 0;
    color: #6b7280;
    text-align: center;
    font-size: 0.95rem;
    line-height: 1.5;
}

.contact-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-label {
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.required {
    color: #ef4444;
    margin-left: 2px;
}

.form-input,
.form-textarea {
    padding: 12px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
}

.form-input.input-error,
.form-textarea.input-error {
    border-color: #ef4444;
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.char-count {
    text-align: right;
    font-size: 0.8rem;
    color: #9ca3af;
    margin-top: 4px;
}

.error-text {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 4px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 12px;
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
}

.btn-cancel,
.btn-submit {
    padding: 12px 28px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-cancel:hover:not(:disabled) {
    background: #e5e7eb;
}

.btn-submit {
    background: #ef4444;
    color: white;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover:not(:disabled) {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-cancel:disabled,
.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.modal-slide-enter-active,
.modal-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-slide-enter-from,
.modal-slide-leave-to {
    opacity: 0;
    transform: translate(-50%, -60%);
}

/* Responsive */
@media (max-width: 992px) {
    .broker-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .broker-contact {
        align-items: center;
    }

    .btn-email {
        margin: 0.75rem auto 0;
    }

    .broker-specialties {
        border-left: none;
        border-top: 2px solid #e5e7eb;
        padding-left: 0;
        padding-top: 1.5rem;
        min-width: auto;
        width: 100%;
        text-align: left;
    }

    .specialties-list li {
        text-align: left;
    }
}

@media (max-width: 768px) {
    .directory-header {
        flex-direction: column;
        align-items: stretch;
    }

    .search-box {
        max-width: 100%;
    }

    .broker-avatar img {
        width: 90px;
        height: 110px;
    }

    .specialties-title {
        text-align: center;
    }
}

@media (max-width: 640px) {
    .modal-container {
        width: 95%;
        max-height: 95vh;
    }

    .modal-header,
    .modal-body {
        padding: 16px;
    }

    .form-actions {
        flex-direction: column-reverse;
    }

    .btn-cancel,
    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>