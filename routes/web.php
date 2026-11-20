<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReviewModerationController;
use App\Http\Controllers\Admin\SettlementController as AdminSettlementController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\CmsPageController as AdminCmsPageController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Owner\AnalyticsController as OwnerAnalyticsController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Owner\RestaurantController as OwnerRestaurantController;
use App\Http\Controllers\Owner\FoodItemController as OwnerFoodItemController;
use App\Http\Controllers\Owner\FoodItemOptionController as OwnerFoodItemOptionController;
use App\Http\Controllers\Owner\DeliveryZoneController as OwnerDeliveryZoneController;
use App\Http\Controllers\Owner\OrderController as OwnerOrderController;
use App\Http\Controllers\Owner\NotificationController as OwnerNotificationController;
use App\Http\Controllers\Owner\SearchController as OwnerSearchController;
use App\Http\Controllers\Owner\RestaurantPhotoController as OwnerRestaurantPhotoController;
use App\Http\Controllers\Owner\ReviewController as OwnerReviewController;
use App\Http\Controllers\Owner\CouponController as OwnerCouponController;
use App\Http\Controllers\Owner\ProfileController as OwnerProfileController;
use App\Http\Controllers\Owner\SettlementController as OwnerSettlementController;
use App\Http\Controllers\Owner\MenuSectionController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\DeliveryAgent\ProfileController as DeliveryProfileController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\LocationController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\OrderController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\Customer\FavoriteController;
use App\Http\Controllers\Customer\CouponController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\RazorpayController;
use App\Http\Controllers\Customer\MembershipController;
use App\Http\Controllers\Customer\SupportTicketController;
use App\Http\Controllers\DeliveryAgent\OrderController as DeliveryOrderController;
use App\Http\Controllers\DeliveryAgent\NotificationController as DeliveryNotificationController;
use App\Http\Controllers\Customer\WalletController as CustomerWalletController;
use App\Http\Controllers\Customer\ReferralController as CustomerReferralController;
use App\Http\Controllers\Customer\DisputeController as CustomerDisputeController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Owner\DisputeController as OwnerDisputeController;
use App\Http\Controllers\Customer\GroupOrderController;
use App\Http\Controllers\Customer\ChatController as CustomerChatController;
use App\Http\Controllers\DeliveryAgent\ChatController as DeliveryChatController;


Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/pages/{slug}', [AdminCmsPageController::class, 'showPublic'])->name('cms.pages.show');

// Stripe calls this directly  must stay outside auth middleware.
// CSRF exemption is registered in bootstrap/app.php.

Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');
Route::post('/razorpay/webhook', [RazorpayController::class, 'webhook'])->name('razorpay.webhook');

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->dashboardRouteName());
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::middleware(['auth', 'isAdmin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('restaurants', AdminRestaurantController::class);
        Route::patch('restaurants/{restaurant}/approve',
            [AdminRestaurantController::class, 'toggleApprove'])
            ->name('restaurants.approve');
        Route::patch('restaurants/{restaurant}/featured',
            [AdminRestaurantController::class, 'toggleFeatured'])
            ->name('restaurants.featured');
        Route::get('/banners', [AdminBannerController::class, 'index'])->name('banners.index');
        Route::post('/banners', [AdminBannerController::class, 'store'])->name('banners.store');
        Route::patch('/banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update');
        Route::delete('/banners/{banner}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');
        Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');
        Route::get('/audit-logs', [AdminSettingsController::class, 'auditLogs'])->name('audit-logs.index');
        Route::get('/support', [AdminSupportTicketController::class, 'index'])->name('support.index');
        Route::patch('/support/{ticket}', [AdminSupportTicketController::class, 'update'])->name('support.update');
        Route::get('/faqs', [AdminFaqController::class, 'index'])->name('faqs.index');
        Route::post('/faqs', [AdminFaqController::class, 'store'])->name('faqs.store');
        Route::patch('/faqs/{faq}', [AdminFaqController::class, 'update'])->name('faqs.update');
        Route::delete('/faqs/{faq}', [AdminFaqController::class, 'destroy'])->name('faqs.destroy');
        Route::get('/pages', [AdminCmsPageController::class, 'index'])->name('pages.index');
        Route::post('/pages', [AdminCmsPageController::class, 'store'])->name('pages.store');
        Route::patch('/pages/{page}', [AdminCmsPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{page}', [AdminCmsPageController::class, 'destroy'])->name('pages.destroy');

        // User & role management (promote to restaurant_owner / delivery_agent / admin)
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
        Route::patch('/users/{user}/kyc', [AdminUserController::class, 'updateKyc'])->name('users.kyc');
        Route::get('/users/{user}/kyc-document', [AdminUserController::class, 'kycDocument'])->name('users.kyc-document');
        Route::get('/review-reports', [ReviewModerationController::class, 'index'])->name('review-reports.index');
        Route::patch('/review-reports/{report}', [ReviewModerationController::class, 'update'])->name('review-reports.update');

        // Coupons / promo codes
        Route::resource('coupons', AdminCouponController::class);

        // Orders (read-only oversight)
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');

        // Disputes / refund requests
        Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
        Route::post('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');

        // Analytics  platform-wide revenue & best-sellers
        Route::get('/analytics/export', [AdminAnalyticsController::class, 'export'])->name('analytics.export');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/settlements', [AdminSettlementController::class, 'index'])->name('settlements.index');
        Route::patch('/settlements/{settlement}', [AdminSettlementController::class, 'update'])->name('settlements.update');

        // Profile (photo, name, phone)
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [AdminProfileController::class, 'update'])->name('profile.update');

        // Notifications  new orders across the whole platform
        Route::get('/notifications', [AdminNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [AdminNotificationController::class, 'markRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllRead'])->name('notifications.readAll');
    });

// Owner Routes
Route::middleware(['auth', 'isRestaurantOwner'])
    ->prefix('owner')->name('owner.')
    ->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

        // Global "search everything"  restaurants, food items, orders
        Route::get('/search', [OwnerSearchController::class, 'index'])->name('search');

        Route::resource('restaurants', OwnerRestaurantController::class)->except(['destroy']);
        Route::patch('restaurants/{restaurant}/toggle',
            [OwnerRestaurantController::class, 'toggleOpen'])
            ->name('restaurants.toggle');

        Route::get('restaurants/{restaurant}/hours', [OwnerRestaurantController::class, 'editHours'])
            ->name('restaurants.hours.edit');
        Route::put('restaurants/{restaurant}/hours', [OwnerRestaurantController::class, 'updateHours'])
            ->name('restaurants.hours.update');

        Route::post('restaurants/{restaurant}/photos', [OwnerRestaurantPhotoController::class, 'store'])
            ->name('restaurants.photos.store');
        Route::delete('restaurants/{restaurant}/photos/{photo}', [OwnerRestaurantPhotoController::class, 'destroy'])
            ->name('restaurants.photos.destroy');

        Route::resource('restaurants.food-items', OwnerFoodItemController::class);
        Route::post('restaurants/{restaurant}/menu-sections', [MenuSectionController::class, 'store'])->name('restaurants.menu-sections.store');
        Route::patch('restaurants/{restaurant}/menu-sections/{section}', [MenuSectionController::class, 'update'])->name('restaurants.menu-sections.update');
        Route::delete('restaurants/{restaurant}/menu-sections/{section}', [MenuSectionController::class, 'destroy'])->name('restaurants.menu-sections.destroy');
        Route::get('restaurants/{restaurant}/food-items-export', [OwnerFoodItemController::class, 'exportMenu'])
            ->name('restaurants.food-items.export');
        Route::patch('restaurants/{restaurant}/food-items/{foodItem}/toggle',
            [OwnerFoodItemController::class, 'toggleAvailability'])
            ->name('restaurants.food-items.toggle');

        // Food item add-ons / variants (e.g. size, extra toppings)
        Route::post('restaurants/{restaurant}/food-items/{foodItem}/options',
            [OwnerFoodItemOptionController::class, 'store'])
            ->name('restaurants.food-items.options.store');
        Route::delete('restaurants/{restaurant}/food-items/{foodItem}/options/{option}',
            [OwnerFoodItemOptionController::class, 'destroy'])
            ->name('restaurants.food-items.options.destroy');

        // Restaurant-wise delivery zones (pincode + fee)
        Route::get('restaurants/{restaurant}/delivery-zones',
            [OwnerDeliveryZoneController::class, 'index'])
            ->name('restaurants.delivery-zones.index');
        Route::post('restaurants/{restaurant}/delivery-zones',
            [OwnerDeliveryZoneController::class, 'store'])
            ->name('restaurants.delivery-zones.store');
        Route::delete('restaurants/{restaurant}/delivery-zones/{zone}',
            [OwnerDeliveryZoneController::class, 'destroy'])
            ->name('restaurants.delivery-zones.destroy');

        // Order management
        Route::get('/orders', [OwnerOrderController::class, 'index'])->name('orders.index');
        Route::patch('/orders/{order}/status', [OwnerOrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('/orders/{order}/print', [OwnerOrderController::class, 'print'])->name('orders.print');

        // Reviews  read + reply
        Route::get('/reviews', [OwnerReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{review}/reply', [OwnerReviewController::class, 'reply'])->name('reviews.reply');

        // Disputesread-only view of complaints on this owner's orders
        Route::get('/disputes', [OwnerDisputeController::class, 'index'])->name('disputes.index');

        // Coupons / promo codes (restaurant-scoped)
        Route::resource('coupons', OwnerCouponController::class)->except(['show']);

        // Notifications
        Route::get('/notifications', [OwnerNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [OwnerNotificationController::class, 'markRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [OwnerNotificationController::class, 'markAllRead'])->name('notifications.readAll');

        // Analytics â€” best-sellers & revenue reports
        Route::get('/analytics', [OwnerAnalyticsController::class, 'index'])->name('analytics');
        Route::get('/settlements', [OwnerSettlementController::class, 'index'])->name('settlements.index');
        Route::post('/settlements', [OwnerSettlementController::class, 'store'])->name('settlements.store');

        // Profile (photo, name, phone)
        Route::get('/profile', [OwnerProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [OwnerProfileController::class, 'update'])->name('profile.update');
    });


//  Delivery Agent Routes

Route::middleware(['auth', 'isDeliveryAgent'])
    ->prefix('delivery')->name('delivery.')
    ->group(function () {
        Route::get('/dashboard', [DeliveryOrderController::class, 'index'])->name('dashboard');
        Route::post('/toggle-online', [DeliveryOrderController::class, 'toggleOnline'])->name('toggleOnline');
        Route::get('/orders/available', [DeliveryOrderController::class, 'available'])->name('orders.available');
        Route::get('/orders/history', [DeliveryOrderController::class, 'history'])->name('orders.history');
        Route::patch('/orders/{order}/accept', [DeliveryOrderController::class, 'accept'])->name('orders.accept');
        Route::patch('/orders/{order}/reject', [DeliveryOrderController::class, 'reject'])->name('orders.reject');
        Route::patch('/orders/{order}/delivered', [DeliveryOrderController::class, 'markDelivered'])->name('orders.delivered');
        Route::post('/orders/{order}/location', [DeliveryOrderController::class, 'updateLocation'])->name('orders.location');

        // Notifications
        Route::get('/notifications', [DeliveryNotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [DeliveryNotificationController::class, 'markRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [DeliveryNotificationController::class, 'markAllRead'])->name('notifications.readAll');

        // Profile (photo, name, phone)
        Route::get('/profile', [DeliveryProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [DeliveryProfileController::class, 'update'])->name('profile.update');

        // Chat with customer
        Route::get('/orders/{order}/chat', [DeliveryChatController::class, 'show'])->name('chat.show');
        Route::post('/orders/{order}/chat', [DeliveryChatController::class, 'send'])->name('chat.send');
        Route::get('/orders/{order}/chat/poll', [DeliveryChatController::class, 'poll'])->name('chat.poll');
        Route::get('/chat/unread-counts', [DeliveryChatController::class, 'unreadCounts'])->name('chat.unread');
    });

// Public Browsing Routes (guest + logged-in customers) 
// Same URLs/names as before (still under /customer/... and named customer.*)
// so every existing route() call in the views keeps working unchanged â€”
// only the auth requirement moves.

Route::prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/search/suggestions', [HomeController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/restaurant/{restaurant}', [HomeController::class, 'show'])->name('restaurant.show');

    // Delivery location (pincode) works for guests too, stored in session.
    Route::post('/location', [LocationController::class, 'update'])->name('location.set');
    Route::delete('/location', [LocationController::class, 'clear'])->name('location.clear');
});

//  Customer Routes 
Route::middleware(['auth', 'isCustomer'])
    ->prefix('customer')->name('customer.')
    ->group(function () {

        Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
        Route::post('/membership/purchase', [MembershipController::class, 'purchase'])->name('membership.purchase');
        Route::get('/support', [SupportTicketController::class, 'index'])->name('support.index');
        Route::post('/support', [SupportTicketController::class, 'store'])->name('support.store');

        // Cart
        Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
        Route::post('/cart/confirm-replace', [CartController::class, 'confirmReplace'])->name('cart.confirmReplace');
        Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::delete('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

        // Orders
        Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
        Route::post('/order/place', [OrderController::class, 'place'])->name('order.place');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('/orders/{order}/status', [OrderController::class, 'status'])->name('orders.status');
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/reorder', [OrderController::class, 'reorder'])->name('orders.reorder');

        // Stripe Payment  {pending} is a PendingCheckout, not an Order yet:
        // the real Order is only created once payment is confirmed.
        Route::get('/payment/{order}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::get('/payment/{order}/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/{order}/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

        // Razorpay Payment  same PendingCheckout flow as Stripe above.
        Route::get('/payment/{order}/razorpay/checkout', [RazorpayController::class, 'checkout'])->name('payment.razorpay.checkout');
        Route::post('/payment/{order}/razorpay/verify', [RazorpayController::class, 'verify'])->name('payment.razorpay.verify');
        Route::get('/payment/{order}/razorpay/cancel', [RazorpayController::class, 'cancel'])->name('payment.razorpay.cancel');

        // Reviews
        Route::post('/orders/{order}/review', [ReviewController::class, 'store'])->name('reviews.store');
        Route::patch('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::post('/reviews/{review}/helpful', [ReviewController::class, 'helpful'])->name('reviews.helpful');
        Route::post('/reviews/{review}/report', [ReviewController::class, 'report'])->name('reviews.report');

        // Addresses
        Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
        Route::post('/addresses', [AddressController::class, 'store'])->name('address.store');
        Route::put('/addresses/{address}', [AddressController::class, 'update'])->name('address.update');
        Route::patch('/addresses/{address}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])->name('addresses.destroy');

        // Profile (photo, name, phone + saved addresses in one place)
        Route::get('/profile', [CustomerProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [CustomerProfileController::class, 'update'])->name('profile.update');

        // Wallet
        Route::get('/wallet', [CustomerWalletController::class, 'index'])->name('wallet.index');
        Route::post('/wallet/topup', [CustomerWalletController::class, 'topup'])->name('wallet.topup');
        Route::post('/wallet/topup/{topup}/verify', [CustomerWalletController::class, 'topupVerify'])->name('wallet.topup.verify');
        Route::get('/wallet/topup/{topup}/cancel', [CustomerWalletController::class, 'topupCancel'])->name('wallet.topup.cancel');

        // Refer & Earn
        Route::get('/refer', [CustomerReferralController::class, 'index'])->name('referral.index');

        // Order issue / dispute reporting
        Route::post('/orders/{order}/dispute', [CustomerDisputeController::class, 'store'])->name('disputes.store');

        // Favorites / Wishlist
        Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('/favorites/{restaurant}/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

        // Coupons / promo codes
        Route::post('/coupon/apply', [CouponController::class, 'apply'])->name('coupon.apply');
        Route::delete('/coupon/remove', [CouponController::class, 'remove'])->name('coupon.remove');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

        // Group Ordering
        Route::post('/restaurant/{restaurant}/group-order/start', [GroupOrderController::class, 'start'])
            ->name('group-orders.start');
        Route::get('/group-order/{code}', [GroupOrderController::class, 'show'])
            ->name('group-orders.show');
        Route::post('/group-order/{code}/items', [GroupOrderController::class, 'addItem'])
            ->name('group-orders.items.add');
        Route::delete('/group-order/{code}/items/{item}', [GroupOrderController::class, 'removeItem'])
            ->name('group-orders.items.remove');
        Route::post('/group-order/{code}/place', [GroupOrderController::class, 'place'])
            ->name('group-orders.place');
        Route::delete('/group-order/{code}', [GroupOrderController::class, 'cancel'])
            ->name('group-orders.cancel');

        // Chat with delivery partner
        Route::get('/orders/{order}/chat', [CustomerChatController::class, 'show'])->name('chat.show');
        Route::post('/orders/{order}/chat', [CustomerChatController::class, 'send'])->name('chat.send');
        Route::get('/orders/{order}/chat/poll', [CustomerChatController::class, 'poll'])->name('chat.poll');
        Route::get('/orders/{order}/chat/unread', [CustomerChatController::class, 'unreadCount'])->name('chat.unread');
    });

require __DIR__.'/auth.php';
