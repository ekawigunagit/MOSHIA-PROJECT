export const statusLabels = {
    pending_payment: 'Menunggu pembayaran',
    payment_expired: 'Pesanan kedaluwarsa',
    awaiting_publish: 'Dibayar · menunggu Publish',
    active: 'Aktif',
    expired: 'Kedaluwarsa',
    cancelled: 'Dibatalkan',
};

export const rupiah = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
}).format(value);

export const dateTime = (value) => value
    ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value))
    : 'Belum dimulai';
