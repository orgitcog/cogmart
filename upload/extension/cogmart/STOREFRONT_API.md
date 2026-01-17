# Shopify Storefront API Integration

## Overview

The CogMart marketplace extension now includes full integration with Shopify's Storefront API, enabling real-time product display from participating marketplace shops.

## Features

### Product Display
- **Real-time fetching** from Shopify Storefront API
- **Product grid layout** with images, prices, and availability
- **Search functionality** to filter products by name/description
- **Sorting options** (title, price, newest, best selling)
- **Pagination** with "Load More" button
- **Responsive design** using Bootstrap 5

### Cart Integration
- **Create carts** via Storefront API
- **Add products** to shop-specific carts
- **Cart line management** (add/update/remove items)
- **Checkout URLs** for each shop's cart

## JavaScript API

### CogMartStorefront Object

The global `CogMartStorefront` object provides methods for interacting with Shopify's Storefront API.

#### Methods

##### fetchProducts(shopDomain, storefrontAccessToken, options)

Fetch products from a shop's Storefront API.

**Parameters:**
- `shopDomain` (string): The shop's myshopify.com domain
- `storefrontAccessToken` (string): The storefront access token
- `options` (object): Optional parameters
  - `first` (number): Number of products to fetch (default: 20)
  - `query` (string): Search query to filter products
  - `sortKey` (string): Sort key (TITLE, PRICE, CREATED_AT, BEST_SELLING)

**Returns:** Promise resolving to products data

**Example:**
```javascript
CogMartStorefront.fetchProducts('example.myshopify.com', 'token123', {
    first: 20,
    query: 'shoes',
    sortKey: 'PRICE'
})
.then(function(products) {
    console.log('Fetched products:', products);
})
.catch(function(error) {
    console.error('Error:', error);
});
```

##### fetchProduct(shopDomain, storefrontAccessToken, handle)

Fetch a single product by its handle.

**Parameters:**
- `shopDomain` (string): The shop's myshopify.com domain
- `storefrontAccessToken` (string): The storefront access token
- `handle` (string): Product handle (URL-friendly identifier)

**Returns:** Promise resolving to product data

**Example:**
```javascript
CogMartStorefront.fetchProduct('example.myshopify.com', 'token123', 'blue-shoes')
.then(function(product) {
    console.log('Product:', product);
});
```

##### createCart(shopDomain, storefrontAccessToken, lines)

Create a new cart with initial line items.

**Parameters:**
- `shopDomain` (string): The shop's myshopify.com domain
- `storefrontAccessToken` (string): The storefront access token
- `lines` (array): Array of line items
  - `merchandiseId` (string): Variant ID
  - `quantity` (number): Quantity to add

**Returns:** Promise resolving to cart data

**Example:**
```javascript
CogMartStorefront.createCart('example.myshopify.com', 'token123', [
    { merchandiseId: 'gid://shopify/ProductVariant/12345', quantity: 1 }
])
.then(function(cart) {
    console.log('Cart created:', cart);
    console.log('Checkout URL:', cart.checkoutUrl);
});
```

##### addCartLines(shopDomain, storefrontAccessToken, cartId, lines)

Add line items to an existing cart.

**Parameters:**
- `shopDomain` (string): The shop's myshopify.com domain
- `storefrontAccessToken` (string): The storefront access token
- `cartId` (string): The cart ID
- `lines` (array): Array of line items to add

**Returns:** Promise resolving to updated cart data

**Example:**
```javascript
CogMartStorefront.addCartLines('example.myshopify.com', 'token123', 'cart-id', [
    { merchandiseId: 'gid://shopify/ProductVariant/67890', quantity: 2 }
])
.then(function(cart) {
    console.log('Cart updated:', cart);
});
```

##### formatPrice(amount, currencyCode)

Format a price with currency symbol.

**Parameters:**
- `amount` (string|number): Price amount
- `currencyCode` (string): Currency code (USD, EUR, etc.)

**Returns:** Formatted price string

**Example:**
```javascript
var price = CogMartStorefront.formatPrice('29.99', 'USD');
// Returns: "$29.99"
```

##### renderProductCard(product, shopDomain)

Generate HTML for a product card.

**Parameters:**
- `product` (object): Product data from Storefront API
- `shopDomain` (string): Shop domain for product links

**Returns:** HTML string for product card

**Example:**
```javascript
var html = CogMartStorefront.renderProductCard(productData, 'example.myshopify.com');
$('#products-container').append(html);
```

## Shop Info Page

The shop info page (`shop_info.twig`) now includes:

1. **Product Search Box** - Filter products by keyword
2. **Sort Dropdown** - Sort by title, price, newest, or best selling
3. **Products Grid** - Displays products in a responsive grid
4. **Load More Button** - Pagination for additional products

### Usage

When viewing a shop at `/index.php?route=extension/cogmart/marketplace/shop.info&marketplace_shop_id=X`:

1. Products automatically load from the shop's Storefront API
2. Use the search box to filter products
3. Change sorting to reorder products
4. Click "Apply" to refresh the product list
5. Click "Load More" to fetch additional products

## Configuration

### Storefront Access Token

Each shop must have a valid Storefront Access Token configured in the admin panel:

1. Navigate to Extensions > Extensions > Modules
2. Click "Manage Shops" on CogMart Marketplace
3. Edit a shop
4. Enter the "Storefront Access Token" field
5. Save the shop

### Obtaining Storefront Access Tokens

For shop owners using Shopify:

1. Log in to your Shopify admin
2. Go to Settings > Apps and sales channels
3. Click "Develop apps" or "Manage private apps"
4. Create a new app or select existing app
5. In the "Storefront API" section, generate an access token
6. Copy the token and provide it to the marketplace administrator
7. Ensure the token has these scopes:
   - `unauthenticated_read_product_listings`
   - `unauthenticated_read_product_inventory`
   - `unauthenticated_read_product_tags`

## API Version

The integration uses Shopify Storefront API version **2024-01**. Update the API version in `storefront-api.js` if needed:

```javascript
// In storefront-api.js, change the API version:
fetch(`https://${shopDomain}/api/2024-01/graphql.json`, {
    // Change to 2024-04, 2024-07, etc.
```

## Error Handling

The integration includes comprehensive error handling:

- **No Access Token**: Shows warning message
- **Network Errors**: Displays error alert with message
- **GraphQL Errors**: Logs errors to console and shows user-friendly message
- **Empty Results**: Shows "No products found" message

## Browser Compatibility

The integration uses modern JavaScript features:

- Fetch API (supported in all modern browsers)
- Promises (supported in all modern browsers)
- Arrow functions (ES6)
- Template literals (ES6)
- Intl.NumberFormat for currency formatting

For older browser support, consider adding polyfills:
- [fetch polyfill](https://github.com/github/fetch)
- [promise polyfill](https://github.com/taylorhakes/promise-polyfill)

## Performance Considerations

### Caching

Consider implementing caching strategies:

1. **Browser caching**: Set appropriate Cache-Control headers
2. **Local storage**: Cache product data locally
3. **Service workers**: Offline support for product listings

### Optimization

Current optimizations:

- **Lazy loading**: Products load on demand
- **Pagination**: Limits initial data transfer (20 products)
- **Efficient queries**: Only fetch required product fields
- **Image optimization**: Shopify CDN delivers optimized images

## Security

### CORS

Shopify's Storefront API includes CORS headers, allowing requests from any origin. This is intentional for public product data.

### Access Token Storage

Storefront access tokens are:
- Stored securely in the database
- Only used for public product data
- Cannot modify shop settings or orders
- Cannot access customer data

### XSS Prevention

The integration uses:
- Twig auto-escaping for template output
- jQuery's text() method for user input
- Sanitized HTML rendering

## Troubleshooting

### Products not loading

1. Verify the shop has a valid Storefront Access Token
2. Check browser console for JavaScript errors
3. Verify the shop domain is correct (including .myshopify.com)
4. Ensure the token has proper API scopes

### CORS errors

If you see CORS errors:
1. Verify the Storefront API is enabled in Shopify
2. Check that the access token is valid
3. Ensure you're using the public Storefront API endpoint

### Rate limiting

Shopify limits API requests. If you encounter rate limits:
1. Implement request throttling
2. Cache product data locally
3. Reduce the number of products fetched per request

## Future Enhancements

Potential improvements:

1. **Product detail pages**: Full product view with all variants
2. **Collections**: Browse products by collection
3. **Advanced filtering**: Price ranges, tags, product types
4. **Wishlist**: Save favorite products
5. **Compare products**: Side-by-side comparison
6. **Reviews integration**: Display product reviews
7. **Image gallery**: Lightbox for product images
8. **Variant selection**: Add to cart with selected options
9. **Real-time inventory**: Show stock levels
10. **Recommendations**: Related products

## Additional Resources

- [Shopify Storefront API Documentation](https://shopify.dev/docs/api/storefront)
- [Storefront API GraphQL Reference](https://shopify.dev/docs/api/storefront/latest)
- [Creating Storefront Access Tokens](https://shopify.dev/docs/custom-storefronts/building-with-the-storefront-api/getting-started)
- [OpenCart Developer Documentation](https://docs.opencart.com/en-gb/developer/)

## Support

For issues related to:
- **CogMart extension**: GitHub Issues at https://github.com/orgitcog/cogmart/issues
- **Shopify Storefront API**: Shopify Community Forums
- **OpenCart platform**: OpenCart Forums at https://forum.opencart.com