/**
 * CogMart Shopify Storefront API Integration
 * 
 * This module provides integration with Shopify's Storefront API
 * to fetch and display products from marketplace participating shops.
 * 
 * @version 2.0.0
 */

(function() {
    'use strict';

    /**
     * CogMart Storefront API Client
     */
    window.CogMartStorefront = {
        /**
         * Escape a string for use in GraphQL queries
         * 
         * @param {string} str - String to escape
         * @returns {string} - Escaped string
         */
        escapeGraphQL: function(str) {
            if (typeof str !== 'string') {
                return '';
            }
            // Escape quotes and backslashes for GraphQL
            return str.replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
        },

        /**
         * Fetch products from a shop's Storefront API
         * 
         * @param {string} shopDomain - The shop's myshopify.com domain
         * @param {string} storefrontAccessToken - The storefront access token
         * @param {object} options - Query options (first, query, sortKey)
         * @returns {Promise} - Promise resolving to product data
         */
        fetchProducts: function(shopDomain, storefrontAccessToken, options) {
            options = options || {};
            const first = parseInt(options.first, 10) || 20;
            const query = this.escapeGraphQL(options.query || '');
            const sortKey = ['TITLE', 'PRICE', 'CREATED_AT', 'BEST_SELLING'].includes(options.sortKey) ? options.sortKey : 'TITLE';
            
            const graphqlQuery = `{
                products(first: ${first}, query: "${query}", sortKey: ${sortKey}) {
                    edges {
                        node {
                            id
                            title
                            handle
                            description
                            descriptionHtml
                            availableForSale
                            vendor
                            productType
                            tags
                            createdAt
                            updatedAt
                            images(first: 5) {
                                edges {
                                    node {
                                        id
                                        url
                                        altText
                                        width
                                        height
                                    }
                                }
                            }
                            priceRange {
                                minVariantPrice {
                                    amount
                                    currencyCode
                                }
                                maxVariantPrice {
                                    amount
                                    currencyCode
                                }
                            }
                            variants(first: 10) {
                                edges {
                                    node {
                                        id
                                        title
                                        availableForSale
                                        price {
                                            amount
                                            currencyCode
                                        }
                                        compareAtPrice {
                                            amount
                                            currencyCode
                                        }
                                        image {
                                            url
                                            altText
                                        }
                                        selectedOptions {
                                            name
                                            value
                                        }
                                    }
                                }
                            }
                        }
                        cursor
                    }
                    pageInfo {
                        hasNextPage
                        hasPreviousPage
                    }
                }
            }`;

            return fetch(`https://${shopDomain}/api/2024-01/graphql.json`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Shopify-Storefront-Access-Token': storefrontAccessToken
                },
                body: JSON.stringify({ query: graphqlQuery })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.errors) {
                    throw new Error('GraphQL errors: ' + JSON.stringify(data.errors));
                }
                return data.data.products;
            });
        },

        /**
         * Fetch a single product by handle
         * 
         * @param {string} shopDomain - The shop's myshopify.com domain
         * @param {string} storefrontAccessToken - The storefront access token
         * @param {string} handle - Product handle
         * @returns {Promise} - Promise resolving to product data
         */
        fetchProduct: function(shopDomain, storefrontAccessToken, handle) {
            const escapedHandle = this.escapeGraphQL(handle);
            
            const graphqlQuery = `{
                productByHandle(handle: "${escapedHandle}") {
                    id
                    title
                    handle
                    description
                    descriptionHtml
                    availableForSale
                    vendor
                    productType
                    tags
                    images(first: 10) {
                        edges {
                            node {
                                id
                                url
                                altText
                                width
                                height
                            }
                        }
                    }
                    priceRange {
                        minVariantPrice {
                            amount
                            currencyCode
                        }
                        maxVariantPrice {
                            amount
                            currencyCode
                        }
                    }
                    variants(first: 100) {
                        edges {
                            node {
                                id
                                title
                                availableForSale
                                quantityAvailable
                                price {
                                    amount
                                    currencyCode
                                }
                                compareAtPrice {
                                    amount
                                    currencyCode
                                }
                                image {
                                    url
                                    altText
                                }
                                selectedOptions {
                                    name
                                    value
                                }
                            }
                        }
                    }
                    options {
                        id
                        name
                        values
                    }
                }
            }`;

            return fetch(`https://${shopDomain}/api/2024-01/graphql.json`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Shopify-Storefront-Access-Token': storefrontAccessToken
                },
                body: JSON.stringify({ query: graphqlQuery })
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    throw new Error('GraphQL errors: ' + JSON.stringify(data.errors));
                }
                return data.data.productByHandle;
            });
        },

        /**
         * Create a cart
         * 
         * @param {string} shopDomain - The shop's myshopify.com domain
         * @param {string} storefrontAccessToken - The storefront access token
         * @param {array} lines - Array of cart line items
         * @returns {Promise} - Promise resolving to cart data
         */
        createCart: function(shopDomain, storefrontAccessToken, lines) {
            lines = lines || [];
            
            const linesInput = lines.map(line => {
                return `{
                    merchandiseId: "${line.merchandiseId}",
                    quantity: ${line.quantity}
                }`;
            }).join(',');

            const graphqlQuery = `
                mutation {
                    cartCreate(input: { lines: [${linesInput}] }) {
                        cart {
                            id
                            checkoutUrl
                            lines(first: 10) {
                                edges {
                                    node {
                                        id
                                        quantity
                                        merchandise {
                                            ... on ProductVariant {
                                                id
                                                title
                                                price {
                                                    amount
                                                    currencyCode
                                                }
                                                product {
                                                    title
                                                    handle
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            cost {
                                totalAmount {
                                    amount
                                    currencyCode
                                }
                                subtotalAmount {
                                    amount
                                    currencyCode
                                }
                            }
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }`;

            return fetch(`https://${shopDomain}/api/2024-01/graphql.json`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Shopify-Storefront-Access-Token': storefrontAccessToken
                },
                body: JSON.stringify({ query: graphqlQuery })
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    throw new Error('GraphQL errors: ' + JSON.stringify(data.errors));
                }
                if (data.data.cartCreate.userErrors.length > 0) {
                    throw new Error('Cart creation errors: ' + JSON.stringify(data.data.cartCreate.userErrors));
                }
                return data.data.cartCreate.cart;
            });
        },

        /**
         * Add lines to an existing cart
         * 
         * @param {string} shopDomain - The shop's myshopify.com domain
         * @param {string} storefrontAccessToken - The storefront access token
         * @param {string} cartId - The cart ID
         * @param {array} lines - Array of cart line items to add
         * @returns {Promise} - Promise resolving to cart data
         */
        addCartLines: function(shopDomain, storefrontAccessToken, cartId, lines) {
            const linesInput = lines.map(line => {
                return `{
                    merchandiseId: "${line.merchandiseId}",
                    quantity: ${line.quantity}
                }`;
            }).join(',');

            const graphqlQuery = `
                mutation {
                    cartLinesAdd(cartId: "${cartId}", lines: [${linesInput}]) {
                        cart {
                            id
                            checkoutUrl
                            lines(first: 50) {
                                edges {
                                    node {
                                        id
                                        quantity
                                        merchandise {
                                            ... on ProductVariant {
                                                id
                                                title
                                                price {
                                                    amount
                                                    currencyCode
                                                }
                                                product {
                                                    title
                                                    handle
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            cost {
                                totalAmount {
                                    amount
                                    currencyCode
                                }
                                subtotalAmount {
                                    amount
                                    currencyCode
                                }
                            }
                        }
                        userErrors {
                            field
                            message
                        }
                    }
                }`;

            return fetch(`https://${shopDomain}/api/2024-01/graphql.json`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Shopify-Storefront-Access-Token': storefrontAccessToken
                },
                body: JSON.stringify({ query: graphqlQuery })
            })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    throw new Error('GraphQL errors: ' + JSON.stringify(data.errors));
                }
                if (data.data.cartLinesAdd.userErrors.length > 0) {
                    throw new Error('Cart errors: ' + JSON.stringify(data.data.cartLinesAdd.userErrors));
                }
                return data.data.cartLinesAdd.cart;
            });
        },

        /**
         * Format price with currency
         * 
         * @param {string} amount - Price amount
         * @param {string} currencyCode - Currency code (USD, EUR, etc.)
         * @returns {string} - Formatted price string
         */
        formatPrice: function(amount, currencyCode) {
            const price = parseFloat(amount);
            
            // Use Intl.NumberFormat for proper currency formatting
            if (typeof Intl !== 'undefined' && Intl.NumberFormat) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: currencyCode
                }).format(price);
            }
            
            // Fallback for older browsers
            return currencyCode + ' ' + price.toFixed(2);
        },

        /**
         * Render product card HTML
         * 
         * @param {object} product - Product data from Storefront API
         * @param {string} shopDomain - Shop domain for linking
         * @returns {string} - HTML string for product card
         */
        renderProductCard: function(product, shopDomain) {
            const node = product.node || product;
            const image = node.images.edges.length > 0 ? node.images.edges[0].node : null;
            const minPrice = node.priceRange.minVariantPrice;
            const maxPrice = node.priceRange.maxVariantPrice;
            
            let priceDisplay = this.formatPrice(minPrice.amount, minPrice.currencyCode);
            if (minPrice.amount !== maxPrice.amount) {
                priceDisplay += ' - ' + this.formatPrice(maxPrice.amount, maxPrice.currencyCode);
            }
            
            const availabilityClass = node.availableForSale ? 'text-success' : 'text-danger';
            const availabilityText = node.availableForSale ? 'In Stock' : 'Out of Stock';
            
            return `
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="card h-100 product-card" data-product-id="${node.id}" data-product-handle="${node.handle}">
                        ${image ? `
                            <img src="${image.url}" class="card-img-top" alt="${image.altText || node.title}" style="height: 200px; object-fit: cover;">
                        ` : `
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fa-solid fa-image fa-3x text-muted"></i>
                            </div>
                        `}
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${node.title}</h5>
                            <p class="card-text flex-grow-1">${node.description ? (node.description.substring(0, 100) + (node.description.length > 100 ? '...' : '')) : ''}</p>
                            <div class="mt-auto">
                                <p class="mb-2"><strong>${priceDisplay}</strong></p>
                                <p class="mb-2 ${availabilityClass}"><small>${availabilityText}</small></p>
                                <a href="https://${shopDomain}/products/${node.handle}" target="_blank" class="btn btn-primary btn-sm w-100">View Product</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
    };
})();
