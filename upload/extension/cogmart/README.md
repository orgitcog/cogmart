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
- **Shop Storefronts**: View individual shop pages with their products
- **Multi-Shop Cart**: Maintain separate carts for each shop
- **Cart Management**: Add, view, and remove items from marketplace carts
- **GraphQL API**: Frontend integration for shop data retrieval

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

### Buyer: Browsing Marketplace

1. **Shop Discovery**
   - Navigate to: `/index.php?route=extension/cogmart/marketplace/shop`
   - Browse all participating marketplace shops
   - Filter by country or search by name
   - Sort by name (A-Z or Z-A)

2. **View Shop Details**
   - Click on any shop to view its storefront
   - See shop name, domain, and country
   - View products from the shop (requires storefront access token)

3. **Multi-Shop Cart**
   - Add products from different shops to your cart
   - Each shop maintains its own cart
   - View all carts: `/index.php?route=extension/cogmart/marketplace/cart`
   - Proceed to checkout for each shop separately

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

**Add to Cart**
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
```

## Integration with Storefront

The extension provides the foundation for marketplace functionality. To fully integrate with Shopify or other storefronts:

1. **Storefront Access Token**: Each shop needs a valid storefront access token
2. **Product Display**: Integrate Shopify Storefront API to fetch and display products
3. **Cart Integration**: Use the Storefront API to create and manage carts
4. **Checkout**: Direct buyers to individual shop checkouts using the stored checkout URLs

## File Structure

```
extension/cogmart/
├── install.json                          # Extension manifest
├── install.sql                           # Database schema
├── admin/
│   ├── controller/module/marketplace.php # Admin controller
│   ├── model/module/marketplace.php      # Admin model
│   └── language/en-gb/module/marketplace.php # Admin language
└── catalog/
    ├── controller/marketplace/
    │   ├── shop.php                      # Shop browsing controller
    │   └── cart.php                      # Cart management controller
    ├── model/marketplace/shop.php        # Catalog model
    └── language/en-gb/marketplace/
        ├── shop.php                      # Shop language
        └── cart.php                      # Cart language
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

## Future Enhancements

Potential improvements:
- Full GraphQL parser implementation
- Real-time product sync from Shopify
- Advanced filtering (price ranges, categories)
- Shop analytics and reporting
- Customer reviews and ratings
- Shop subscription/commission tracking
- Multi-language support for shop descriptions
- Image uploads for shop branding

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
