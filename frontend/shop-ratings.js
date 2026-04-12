/**
 * Shop & Product Ratings System
 * Handles rating display, submission, and reviews for shops and products
 */

class ShopRatingsManager {
    constructor() {
        this.shopRatings = {};
        this.productRatings = {};
    }

    /**
     * Fetch ratings for a specific shop
     */
    async getShopRatings(shopId) {
        try {
            const response = await fetch(`/capstone/backend/ratings_api.php?action=get_target_ratings&target_type=shop&target_id=${shopId}`);
            const data = await response.json();
            if (data.reviews) {
                this.shopRatings[shopId] = data.reviews;
                return data.reviews;
            }
            return [];
        } catch (error) {
            console.error('Error fetching shop ratings:', error);
            return [];
        }
    }

    /**
     * Fetch ratings for a specific product
     */
    async getProductRatings(productId) {
        try {
            const response = await fetch(`/capstone/backend/ratings_api.php?action=get_target_ratings&target_type=product&target_id=${productId}`);
            const data = await response.json();
            if (data.reviews) {
                this.productRatings[productId] = data.reviews;
                return data.reviews;
            }
            return [];
        } catch (error) {
            console.error('Error fetching product ratings:', error);
            return [];
        }
    }

    /**
     * Get rating statistics for a shop or product
     */
    async getRatingStats(targetType, targetId) {
        try {
            const response = await fetch(`/capstone/backend/ratings_api.php?action=get_target_ratings&target_type=${targetType}&target_id=${targetId}`);
            const data = await response.json();
            return data.statistics || null;
        } catch (error) {
            console.error('Error fetching rating stats:', error);
            return null;
        }
    }

    /**
     * Submit a rating/feedback for a shop or product
     */
    async submitRating(ratingData) {
        try {
            const response = await fetch('/capstone/backend/ratings_api.php?action=submit_rating', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(ratingData)
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error submitting rating:', error);
            return { success: false, error: error.message };
        }
    }

    /**
     * Create star rating HTML
     */
    createStarRating(rating, count = 0) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        let stars = '';

        for (let i = 0; i < 5; i++) {
            if (i < fullStars) {
                stars += '<span style="color:#fbbf24; font-size:16px;">★</span>';
            } else if (i === fullStars && hasHalfStar) {
                stars += '<span style="color:#fbbf24; font-size:16px;">★</span>';
            } else {
                stars += '<span style="color:#e5e7eb; font-size:16px;">★</span>';
            }
        }

        const countText = count > 0 ? `<span style="margin-left:8px; color:#666; font-size:14px;">(${count} reviews)</span>` : '';
        return `<div style="display:flex; align-items:center; gap:4px;">${stars}${countText}</div>`;
    }

    /**
     * Create rating display component
     */
    createRatingDisplay(stats) {
        if (!stats) {
            return `
                <div style="text-align:center; padding:20px; background:#f9fafb; border-radius:10px;">
                    <p style="color:#666; margin:0; font-size:14px;">No ratings yet. Be the first to review!</p>
                </div>
            `;
        }

        const avgRating = stats.average_rating ? parseFloat(stats.average_rating).toFixed(1) : 0;
        const totalReviews = stats.total_reviews || 0;
        const positiveReviews = stats.positive_reviews || 0;
        const neutralReviews = stats.neutral_reviews || 0;
        const negativeReviews = stats.negative_reviews || 0;

        const positivePercent = totalReviews > 0 ? Math.round((positiveReviews / totalReviews) * 100) : 0;
        const neutralPercent = totalReviews > 0 ? Math.round((neutralReviews / totalReviews) * 100) : 0;
        const negativePercent = totalReviews > 0 ? Math.round((negativeReviews / totalReviews) * 100) : 0;

        return `
            <div style="background:#f9fafb; border-radius:12px; padding:20px;">
                <!-- Overall Rating -->
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #e5e7eb;">
                    <div style="text-align:center;">
                        <div style="font-size:32px; font-weight:800; color:#1f2937;">${avgRating}</div>
                        <div style="display:flex; justify-content:center; margin:6px 0 6px;">
                            ${this.createStarRating(parseFloat(avgRating))}
                        </div>
                        <div style="font-size:13px; color:#666;">${totalReviews} review${totalReviews !== 1 ? 's' : ''}</div>
                    </div>
                </div>

                <!-- Rating Breakdown -->
                <div style="margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                        <span style="font-size:14px; color:#666; min-width:80px;">😊 Positive</span>
                        <div style="flex:1; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:#10b981; width:${positivePercent}%;"></div>
                        </div>
                        <span style="font-size:13px; color:#666; min-width:40px;">${positivePercent}%</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
                        <span style="font-size:14px; color:#666; min-width:80px;">😐 Neutral</span>
                        <div style="flex:1; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:#f59e0b; width:${neutralPercent}%;"></div>
                        </div>
                        <span style="font-size:13px; color:#666; min-width:40px;">${neutralPercent}%</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="font-size:14px; color:#666; min-width:80px;">😞 Negative</span>
                        <div style="flex:1; height:8px; background:#e5e7eb; border-radius:4px; overflow:hidden;">
                            <div style="height:100%; background:#ef4444; width:${negativePercent}%;"></div>
                        </div>
                        <span style="font-size:13px; color:#666; min-width:40px;">${negativePercent}%</span>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Create feedback card for display
     */
    createFeedbackCard(feedback) {
        const date = new Date(feedback.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });

        const userName = feedback.anonymous ? 'Anonymous' : (feedback.user_name || 'Guest');

        return `
            <div style="background:white; border:1px solid #e5e7eb; border-radius:10px; padding:16px; margin-bottom:12px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px;">
                    <div>
                        <h4 style="margin:0; color:#1f2937; font-weight:700; font-size:15px;">${userName}</h4>
                        <small style="color:#999; font-size:12px;">${date}</small>
                    </div>
                    <div style="text-align:right;">
                        ${this.createStarRating(feedback.rating)}
                    </div>
                </div>
                <p style="margin:0; color:#4b5563; font-size:14px; line-height:1.5;">${this.sanitizeHtml(feedback.message)}</p>
            </div>
        `;
    }

    /**
     * Sanitize HTML to prevent XSS
     */
    sanitizeHtml(html) {
        const div = document.createElement('div');
        div.textContent = html;
        return div.innerHTML;
    }

    /**
     * Open feedback form for shop
     */
    openShopFeedbackForm(shopId, shopName) {
        if (window.feedbackSystem) {
            window.feedbackSystem.openModalForTarget('shop', shopId, shopName);
        }
    }

    /**
     * Open feedback form for product (with optional parent shop context)
     */
    openProductFeedbackForm(productId, productName, parentShopId = null) {
        if (window.feedbackSystem) {
            window.feedbackSystem.openModalForTarget('product', productId, productName, parentShopId);
        }
    }
}

// Initialize on page load
let shopRatingsManager = new ShopRatingsManager();
