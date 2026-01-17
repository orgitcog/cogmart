# CogMart Marketplace Extension

This extension adds Shopify-like marketplace functionality to OpenCart, allowing multiple shops to participate in a marketplace platform similar to the Shopify Marketplaces Admin and Buyer apps.

## Features

### Admin/Merchant Features (from shopify-marketplaces-admin-app)
- **Shop Management**: Add, edit, and manage marketplace participant shops
- **Shop Registration**: Register shops with their domain, name, country, and storefront access token
- **Onboarding Workflow**: Track onboarding status (info completed, terms accepted, onboarding completed)
- **GraphQL-like API**: Query shops, countries, and shop details via API endpoints
- **Multi-shop Support**: Manage multiple participating shops in the marketplace

### Buyer Features (from shopify-marketplaces-buyer-app)
- **Shop Discovery**: Browse all participating shops in the marketplace
- **Shop Filtering**: Filter shops by country and search by name
- **Shop Storefronts**: View individual shop pages with live product displays
- **Product Display**: Real-time product fetching from Shopify Storefront API
- **Product Search**: Search products across all marketplace shops
- **Advanced Filtering**: Filter by country, price range, availability, and sort options
- **Multi-Shop Cart**: Maintain separate carts for each shop
- **Cart Management**: Add, view, and remove items from marketplace carts
- **Enhanced UI**: Modern Bootstrap 5 interface with responsive design

### New Features (v2.0)

#### ✅ Shopify Storefront API Integration
- **Real-time product fetching** from participating Shopify stores
- **Product grid display** with images, prices, and availability
- **Product search and filtering** within individual shops
- **Multiple sort options** (title, price, newest, best selling)
- **Pagination** with "Load More" functionality
- **Currency formatting** with international support
- **Direct product links** to shop storefronts

#### ✅ Global Product Search
- **Cross-shop search** - Search products across all marketplace shops simultaneously
- **Multi-criteria filtering**:
  - Text search (product name, description, tags)
  - Country filter (filter shops by location)
  - Price range (min/max price filters)
  - Availability filter (in stock only)
  - Sort options (title, price, newest)
- **Results aggregation** - Unified display of products from multiple shops
- **Shop attribution** - Each product shows which shop it's from
- **Search statistics** - Shows count of products found from how many shops

#### ✅ Enhanced Cart Management
- **Improved cart UI** - Card-based layout for better organization
- **Cart metadata** - Display shop names, domains, and cart IDs
- **API endpoint** - Programmatic cart creation and updates
- **CORS support** - Cross-origin requests for frontend integration
- **Cart persistence** - Maintains carts across sessions
- **Checkout integration** - Direct links to shop checkouts

## Installation

1. **Upload Extension Files**
   - Copy the `cogmart` folder to `upload/extension/`
   - Ensure proper file permissions (755 for directories, 644 for files)

2. **Run Database Installation**
   - Navigate to Extensions > Extensions > Modules
   - Find "CogMart Marketplace" in the list
   - Click the "Install" button
   - This will create the necessary database tables:
     - `oc_marketplace_shop`: Stores marketplace shop information
     - `oc_marketplace_cart`: Stores multi-shop cart data

3. **Enable the Module**
   - After installation, click "Edit" on the module
   - Set Status to "Enabled"
   - Save the settings

## Database Schema

### marketplace_shop Table
```sql
- marketplace_shop_id (PK)
- domain (unique)
- name
- country
- storefront_access_token
- onboarding_info_completed
- terms_accepted
- onboarding_completed
- status
- date_added
- date_modified
```

### marketplace_cart Table
```sql
- marketplace_cart_id (PK)
- customer_id (nullable)
- session_id
- marketplace_shop_id (FK)
- cart_id (external cart reference)
- checkout_url
- date_added
- date_modified
```

## Usage

### Admin: Managing Shops

1. **Access Shop Management**
   - Go to Extensions > Extensions > Modules
   - Click "Manage Shops" on the Marketplace module

2. **Add a New Shop**
   - Click "Add Shop"
   - Enter shop details:
     - Name: Display name of the shop
     - Domain: Shop's domain (e.g., example.myshopify.com)
     - Country: Shop's country
     - Storefront Access Token: API token for accessing shop data
   - Set Status to "Enabled"
   - Click "Save"

3. **Edit Existing Shop**
   - Click "Edit" next to any shop in the list
   - Update shop information
   - Click "Save"

4. **Delete Shops**
   - Select shops using checkboxes
   - Click "Delete" button
   - Confirm deletion

### Buyer: Using the Marketplace

1. **Browse Shops**
   - Navigate to: `/index.php?route=extension/cogmart/marketplace/shop`
   - Browse all participating marketplace shops
   - Filter by country or search by shop name
   - Sort by name (A-Z or Z-A)
   - Click on any shop to view its products

2. **View Shop Products**
   - Click on any shop to see its storefront
   - Products are loaded in real-time from the shop's Shopify store
   - Use the search box to filter products within the shop
   - Change sort order (title, price, newest, best selling)
   - Click "Apply" to refresh the product list
   - Click "Load More" to fetch additional products
   - Click on product images or "View Product" to visit the product page on the shop's storefront

3. **Search Products Globally**
   - Click "Search Products" button on the shop listing page
   - Or navigate to: `/index.php?route=extension/cogmart/marketplace/search`
   - Enter search terms to find products across all shops
   - Use filters to refine results:
     - Country: Filter shops by location
     - Price Range: Set minimum and maximum price
     - Availability: Show only in-stock items
     - Sort: Order by title, price, or newest
   - View search statistics showing products found from multiple shops
   - Each product card shows which shop it's from
   - Click "View Product" to visit the product on the shop's storefront

4. **Multi-Shop Cart**
   - Add products from different shops to your cart
   - Each shop maintains its own separate cart
   - View all carts: `/index.php?route=extension/cogmart/marketplace/cart`
   - Cart page shows:
     - Cart details for each shop
     - Shop names and domains
     - Links to continue shopping in each shop
     - Checkout buttons for each shop
   - Click "Go to Checkout" to proceed to the shop's checkout
   - Remove carts using the "Remove Cart" button (with confirmation)

## API Endpoints

### GraphQL-like Shop Query API
**Endpoint**: `/index.php?route=extension/cogmart/marketplace/shop.api`

**Query Examples**:

1. **Get All Shops**
```json
POST /index.php?route=extension/cogmart/marketplace/shop.api
{
  "query": "query { shops { id domain name country storefrontAccessToken } }"
}
```

2. **Get Shops by Country**
```json
POST /index.php?route=extension/cogmart/marketplace/shop.api
{
  "query": "query { shops { id domain name country } }",
  "variables": {
    "country": "United States"
  }
}
```

3. **Get Single Shop**
```json
POST /index.php?route=extension/cogmart/marketplace/shop.api
{
  "query": "query { shop(id: $id) { id domain name country storefrontAccessToken } }",
  "variables": {
    "id": 1
  }
}
```

4. **Get Shop Countries**
```json
POST /index.php?route=extension/cogmart/marketplace/shop.api
{
  "query": "query { shopCountries }"
}
```

### Cart API

**Create/Update Cart (API)**
```
POST /index.php?route=extension/cogmart/marketplace/cart.api
Content-Type: application/json

{
  "marketplace_shop_id": 1,
  "cart_id": "gid://shopify/Cart/abc123",
  "checkout_url": "https://shop.myshopify.com/checkouts/abc123"
}

Response:
{
  "success": true,
  "action": "created" | "updated",
  "marketplace_cart_id": 123,
  "message": "Success: You have added item to your marketplace cart!",
  "total_carts": 2
}
```

**Add to Cart (Legacy)**
```
POST /index.php?route=extension/cogmart/marketplace/cart.add
{
  "marketplace_shop_id": 1,
  "cart_id": "external_cart_id",
  "checkout_url": "https://shop.domain/checkout"
}
```

**Get Cart Count**
```
GET /index.php?route=extension/cogmart/marketplace/cart.count

Response:
{
  "total": 2
}
```

## Integration with Shopify Storefront

The extension now includes **full Shopify Storefront API integration**. See [STOREFRONT_API.md](STOREFRONT_API.md) for complete documentation.

### Quick Start

1. **Configure Shop**: Add shop with valid Storefront Access Token in admin panel
2. **Automatic Product Display**: Products automatically load when viewing shop pages
3. **Search Integration**: Products appear in global search results
4. **Cart Creation**: Use the JavaScript API to create carts and add products

### JavaScript API

The extension includes `CogMartStorefront` JavaScript object for easy integration:

```javascript
// Fetch products
CogMartStorefront.fetchProducts('shop.myshopify.com', 'token', {
    first: 20,
    query: 'shoes',
    sortKey: 'PRICE'
}).then(products => {
    console.log('Products:', products);
});

// Create cart
CogMartStorefront.createCart('shop.myshopify.com', 'token', [
    { merchandiseId: 'gid://shopify/ProductVariant/123', quantity: 1 }
]).then(cart => {
    console.log('Cart:', cart.checkoutUrl);
});
```

### Obtaining Storefront Access Tokens

For shop owners using Shopify:

1. Log in to Shopify admin
2. Go to Settings > Apps and sales channels
3. Click "Develop apps"
4. Create new app with Storefront API access
5. Generate Storefront Access Token
6. Provide token to marketplace administrator
7. Required scopes:
   - `unauthenticated_read_product_listings`
   - `unauthenticated_read_product_inventory`
   - `unauthenticated_read_product_tags`

## File Structure

```
extension/cogmart/
├── install.json                          # Extension manifest
├── install.sql                           # Database schema
├── README.md                             # This file
├── STOREFRONT_API.md                     # Storefront API integration guide
├── FEATURE_MAPPING.md                    # Feature mapping from Shopify Kit
├── IMPLEMENTATION_SUMMARY.md             # Implementation summary
├── admin/
│   ├── controller/module/marketplace.php # Admin controller
│   ├── model/module/marketplace.php      # Admin model
│   ├── language/en-gb/module/marketplace.php # Admin language
│   └── view/template/module/             # Admin templates
│       ├── marketplace.twig              # Module settings
│       ├── marketplace_shop_form.twig    # Shop add/edit form
│       └── marketplace_shop_list.twig    # Shop list
└── catalog/
    ├── controller/marketplace/
    │   ├── shop.php                      # Shop browsing & API controller
    │   ├── cart.php                      # Cart management controller
    │   └── search.php                    # Global product search controller
    ├── model/marketplace/shop.php        # Catalog model
    ├── language/en-gb/marketplace/
    │   ├── shop.php                      # Shop language strings
    │   ├── cart.php                      # Cart language strings
    │   └── search.php                    # Search language strings
    └── view/template/marketplace/
        ├── shop_list.twig                # Shop listing page
        ├── shop_info.twig                # Individual shop page with products
        ├── cart.twig                     # Multi-shop cart page
        └── search.twig                   # Global search page

upload/catalog/view/javascript/
└── cogmart-storefront-api.js             # Shopify Storefront API client (installed separately)
```

## Architecture

### Based on Shopify Marketplace Kit

This extension implements PHP equivalents of:

**Admin App Features** (from shopify-marketplaces-admin-app):
- Shop registration and management
- Storefront access token handling
- GraphQL query resolvers for shop data
- Onboarding workflow tracking
- Webhook handlers (shop updates, uninstall)

**Buyer App Features** (from shopify-marketplaces-buyer-app):
- Shop discovery and browsing
- Shop filtering and sorting
- Product display integration points
- Multi-shop cart management
- Checkout URL handling

### OpenCart Integration

The extension follows OpenCart 4.x conventions:
- Namespaced classes (`Opencart\Admin\Controller\Extension\Cogmart\Module\Marketplace`)
- MVC-A pattern with Action-based routing
- Registry pattern for service access
- Event system integration points
- Standard OpenCart database practices

## Customization

### Adding Custom Fields

To add custom fields to shops:

1. Add columns to `oc_marketplace_shop` table
2. Update model methods in `admin/model/module/marketplace.php` and `catalog/model/marketplace/shop.php`
3. Add form fields in admin controller and views
4. Update language files with new labels

### Extending API

The API endpoint in `catalog/controller/marketplace/shop.php` can be extended with:
- Additional query types
- Mutation support for updates
- More complex filtering options
- GraphQL schema validation

## Security Considerations

1. **Input Validation**: All user inputs are escaped using `$this->db->escape()`
2. **Permission Checks**: Admin actions require proper user permissions
3. **SQL Injection**: Prepared queries and integer casting prevent SQL injection
4. **Access Tokens**: Store storefront access tokens securely
5. **CORS**: API endpoints include CORS headers for frontend integration

## Troubleshooting

### Tables Not Created
- Manually run the SQL from `install.sql`
- Check database user permissions
- Verify DB_PREFIX setting matches your configuration

### Permission Errors
- Grant modify permissions to admin user group for the marketplace module
- Check: System > Users > User Groups > Edit > Modify Permissions

### API Not Working
- Verify module is enabled
- Check shop status is set to "Enabled"
- Ensure proper Content-Type headers in API requests

## What's New in v2.0

### ✅ Completed Features
- ✅ **Full Shopify Storefront API integration** - Real-time product display
- ✅ **Product search and filtering** - Within individual shops
- ✅ **Global product search** - Search across all marketplace shops
- ✅ **Advanced filtering** - Price ranges, availability, country filters
- ✅ **Enhanced cart management** - Improved UI with cart details
- ✅ **JavaScript API client** - Easy integration with `CogMartStorefront` object
- ✅ **Multiple sort options** - Sort by title, price, date, best selling
- ✅ **Pagination** - Load more products on demand
- ✅ **Currency formatting** - International currency support
- ✅ **Responsive design** - Modern Bootstrap 5 interface

### Roadmap

Potential future improvements:
- [ ] Full GraphQL parser implementation
- [ ] Product detail pages with variant selection
- [ ] Customer reviews and ratings system
- [ ] Shop analytics dashboard (admin)
- [ ] Commission tracking and payouts
- [ ] Featured shops functionality
- [ ] Shop categories and tags
- [ ] Wishlist functionality
- [ ] Product comparison
- [ ] Multi-language support for shop descriptions
- [ ] Shop branding (logos, banners)
- [ ] Email notifications for orders
- [ ] Advanced reporting and analytics
- [ ] Mobile app integration
- [ ] SEO optimization for product pages

## License

This extension follows the same license as OpenCart (GPL-3.0)

## Support

For issues and questions:
- Check OpenCart forums: https://forum.opencart.com
- GitHub Issues: https://github.com/orgitcog/cogmart/issues
- OpenCart documentation: https://docs.opencart.com

## Credits

Based on Shopify Marketplace Kit:
- https://github.com/orgitcog/shopify-marketplaces-admin-app
- https://github.com/orgitcog/shopify-marketplaces-buyer-app

Adapted for OpenCart by the CogMart team.
