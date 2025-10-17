import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

// Global cart state
const cartItems = ref([]);
const isCartOpen = ref(false);

// Load cart from localStorage on initialization
const loadCartFromStorage = () => {
    try {
        const stored = localStorage.getItem('equipment_cart');
        if (stored) {
            cartItems.value = JSON.parse(stored);
        }
    } catch (error) {
        console.error('Error loading cart from storage:', error);
        cartItems.value = [];
    }
};

// Save cart to localStorage
const saveCartToStorage = () => {
    try {
        localStorage.setItem('equipment_cart', JSON.stringify(cartItems.value));
    } catch (error) {
        console.error('Error saving cart to storage:', error);
    }
};

// Watch for cart changes and save to storage
watch(cartItems, saveCartToStorage, { deep: true });

export function useCart() {
    // Load cart on first use
    if (cartItems.value.length === 0) {
        loadCartFromStorage();
    }

    // Computed properties
    const totalItems = computed(() => {
        return cartItems.value.reduce((total, item) => total + item.quantity, 0);
    });

    const isEmpty = computed(() => cartItems.value.length === 0);

    // Send cart notification to admin
    const sendCartNotification = async (equipment, quantity) => {
        try {
            await axios.post('/admin/notifications/cart-addition', {
                equipment_id: equipment.id,
                equipment_name: equipment.name,
                quantity: quantity
            });
        } catch (error) {
            // Silently fail - don't interrupt user experience
            console.error('Failed to send cart notification:', error);
        }
    };

    // Add item to cart
    const addToCart = (equipment, quantity = 1) => {
        const existingItem = cartItems.value.find(item => item.id === equipment.id);
        const addedQuantity = quantity;

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cartItems.value.push({
                id: equipment.id,
                name: equipment.name,
                description: equipment.description,
                image: equipment.image,
                category: equipment.category || equipment.equipment_category,
                location: equipment.location,
                quantity: quantity,
                addedAt: new Date().toISOString()
            });
        }

        // Send notification to admin (async, don't wait)
        sendCartNotification(equipment, addedQuantity);
    };

    // Remove item from cart
    const removeFromCart = (equipmentId) => {
        const index = cartItems.value.findIndex(item => item.id === equipmentId);
        if (index > -1) {
            cartItems.value.splice(index, 1);
        }
    };

    // Update item quantity
    const updateQuantity = (equipmentId, newQuantity) => {
        if (newQuantity <= 0) {
            removeFromCart(equipmentId);
            return;
        }

        const item = cartItems.value.find(item => item.id === equipmentId);
        if (item) {
            item.quantity = newQuantity;
        }
    };

    // Clear entire cart
    const clearCart = () => {
        cartItems.value = [];
    };

    // Check if item is in cart
    const isInCart = (equipmentId) => {
        return cartItems.value.some(item => item.id === equipmentId);
    };

    // Get item quantity in cart
    const getItemQuantity = (equipmentId) => {
        const item = cartItems.value.find(item => item.id === equipmentId);
        return item ? item.quantity : 0;
    };

    // Cart modal controls
    const openCart = () => {
        isCartOpen.value = true;
    };

    const closeCart = () => {
        isCartOpen.value = false;
    };

    // Proceed to checkout
    const proceedToCheckout = () => {
        if (isEmpty.value) {
            alert('Your cart is empty!');
            return;
        }

        // Navigate to checkout page - cart data will be loaded from localStorage
        router.visit(route('student.cart.checkout'));
    };

    return {
        // State
        cartItems: computed(() => cartItems.value),
        isCartOpen: computed(() => isCartOpen.value),

        // Computed
        totalItems,
        isEmpty,

        // Methods
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        isInCart,
        getItemQuantity,
        openCart,
        closeCart,
        proceedToCheckout
    };
}