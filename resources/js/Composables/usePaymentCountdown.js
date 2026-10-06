import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

export function paymentCountdown(order, now) {
    const seconds = Math.max(0, Math.ceil((Date.parse(order.payment_expires_at) - now) / 1000));
    const status = order.status === 'pending_payment' && seconds === 0 ? 'payment_expired' : order.status;
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    return {
        ...order,
        status,
        countdown: [hours, minutes, seconds % 60].map((value) => String(value).padStart(2, '0')).join(':'),
    };
}

export function usePaymentCountdown(getOrders, getServerNow) {
    const now = ref(0);
    let serverTime = 0;
    let receivedAt = 0;
    let timer;
    // Use server time plus elapsed monotonic time, not the user's wall clock.
    const tick = () => { now.value = serverTime + (performance.now() - receivedAt); };
    watch(getServerNow, (value) => {
        serverTime = Date.parse(value);
        receivedAt = performance.now();
        tick();
    }, { immediate: true });
    onMounted(() => {
        timer = window.setInterval(tick, 1000);
        document.addEventListener('visibilitychange', tick);
    });
    onUnmounted(() => {
        window.clearInterval(timer);
        document.removeEventListener('visibilitychange', tick);
    });

    return computed(() => getOrders().map((order) => paymentCountdown(order, now.value)));
}
