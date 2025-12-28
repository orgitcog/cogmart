# Feature Mapping: Shopify Marketplace Kit → CogMart Extension

This document maps the features from the Shopify Marketplace Kit Node.js applications to their PHP implementations in the CogMart marketplace extension.

## Admin App (shopify-marketplaces-admin-app) → Admin Extension

### Database Models

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `models/shop.js` (Sequelize) | `admin/model/module/marketplace.php` | Shop data model with CRUD operations |
| `models/session.js` (Sequelize) | OpenCart's built-in session system | Session management |

### GraphQL Schema & Resolvers

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `server/graphql/schema.js` → `AdminShop` type | `admin/model/module/marketplace.php` → Shop model | Shop administrative data |
| `server/graphql/schema.js` → `Shop` type | `catalog/model/marketplace/shop.php` → Shop model | Public shop data |
| `server/graphql/resolvers.js` → `Query.adminShop` | `admin/controller/module/marketplace.php` → Various methods | Admin shop queries |
| `server/graphql/resolvers.js` → `Query.shops` | `catalog/controller/marketplace/shop.php` → `api()` method | Public shop listing |
| `server/graphql/resolvers.js` → `Query.shopCountries` | `catalog/controller/marketplace/shop.php` → `api()` method | Country filter list |
| `server/graphql/resolvers.js` → `Mutation.completeOnboardingInfo` | `admin/controller/module/marketplace.php` → `saveShop()` | Shop onboarding workflow |

### Handlers (API Integration)

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `server/handlers/queries/get-storefront-access-token.js` | `admin/model/module/marketplace.php` → `getShop()` | Retrieve storefront token |
| `server/handlers/mutations/create-storefront-access-token.js` | `admin/controller/module/marketplace.php` → `saveShop()` | Store storefront token |
| `server/handlers/queries/get-shop-details.js` | `admin/model/module/marketplace.php` → `getShop()` | Retrieve shop details |
| `server/handlers/rest/get-product-listings-count.js` | Placeholder in shop info template | Product count (Storefront API) |

### Admin UI

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `app/` (React components) | `admin/view/template/module/` (Twig templates) | Admin interface |
| Shop management UI | `marketplace_shop_list.twig` | List and manage shops |
| Shop edit form | `marketplace_shop_form.twig` | Add/edit shop details |
| Module settings | `marketplace.twig` | Enable/disable module |

## Buyer App (shopify-marketplaces-buyer-app) → Catalog Extension

### Shop Discovery

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `pages/index.js` → SHOPS_QUERY | `catalog/controller/marketplace/shop.php` → `index()` | Browse all shops |
| `pages/index.js` → SHOP_COUNTRIES | `catalog/model/marketplace/shop.php` → `getCountries()` | Country filter |
| `pages/index.js` → SubHeader component | `shop_list.twig` → Filter UI | Search and filter interface |
| `pages/index.js` → ShopsSection | `shop_list.twig` → Shop grid | Shop listing with pagination |

### Shop Storefront

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `pages/shops/[id].js` → SHOP_QUERY | `catalog/controller/marketplace/shop.php` → `info()` | Individual shop page |
| `pages/shops/[id].js` → SHOP_DETAILS_QUERY | `shop_info.twig` → Storefront API call | Shop details and products |
| `pages/shops/[id].js` → SHOP_PRODUCTS_QUERY | `shop_info.twig` → JavaScript placeholder | Product listing (Storefront API) |
| `pages/shops/[id].js` → CategoryTabs | `shop_info.twig` → Integration point | Product categories |
| `pages/shops/[id].js` → ProductFilters | `shop_info.twig` → Integration point | Price/availability filters |

### Product Page

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `pages/products/[shopid]/[producthandle].js` | `shop_info.twig` → Product integration | Product detail page |
| PRODUCT_PAGE_QUERY | Storefront API integration | Product data |
| RECOMMENDATIONS_QUERY | Storefront API integration | Recommended products |
| Variant selection | JavaScript in template | Product options |

### Cart & Checkout

| Shopify Node.js | CogMart PHP | Description |
|----------------|-------------|-------------|
| `helpers/cartHelpers.js` → `addToCart()` | `catalog/controller/marketplace/cart.php` → `add()` | Add to cart |
| `helpers/cartHelpers.js` → `getCarts()` | `catalog/model/marketplace/shop.php` → `getCarts()` | Get all carts |
| `helpers/cartHelpers.js` → CART_CREATE_MUTATION | `catalog/model/marketplace/shop.php` → `addToCart()` | Create cart |
| `helpers/cartHelpers.js` → CART_LINES_ADD_MUTATION | `catalog/model/marketplace/shop.php` → `updateCart()` | Add cart items |
| `helpers/cartHelpers.js` → CART_LINES_REMOVE_MUTATION | `catalog/controller/marketplace/cart.php` → `remove()` | Remove from cart |
| `pages/cart/index.js` | `catalog/view/template/marketplace/cart.twig` | Cart view |

## Architecture Differences

### Technology Stack

| Shopify Node.js | CogMart PHP | Notes |
|----------------|-------------|-------|
| Express.js | OpenCart MVC-A Framework | Web framework |
| React | Twig Templates + Bootstrap 5 | Frontend |
| Apollo GraphQL | Custom GraphQL-like API | API layer |
| Sequelize ORM | OpenCart DB layer | Database access |
| Webpack | OpenCart asset pipeline | Build system |

### Data Flow

**Shopify Node.js:**
```
React Component → Apollo Client → GraphQL Server → Sequelize Model → PostgreSQL
```

**CogMart PHP:**
```
Twig Template → AJAX/Form → Controller → Model → MySQL (via OpenCart DB)
```

### API Endpoints

**Shopify Node.js:**
- `/graphql` - Single GraphQL endpoint
- `/webhooks` - Webhook receiver

**CogMart PHP:**
- `/index.php?route=extension/cogmart/marketplace/shop.api` - GraphQL-like queries
- `/index.php?route=extension/cogmart/marketplace/cart.add` - Cart operations
- Standard OpenCart routing for pages

## Key Features Implemented

### ✅ Fully Implemented

1. **Shop Management (Admin)**
   - Add/Edit/Delete shops
   - Store storefront access tokens
   - Track onboarding status
   - Country/domain management

2. **Shop Discovery (Buyer)**
   - Browse all marketplace shops
   - Filter by country
   - Search by name
   - Sort by name

3. **Multi-Shop Cart**
   - Separate carts per shop
   - Session/customer tracking
   - Cart count
   - Checkout URL storage

4. **API Layer**
   - GraphQL-like query endpoint
   - Shop queries (shops, shop, shopCountries)
   - CORS support
   - JSON responses

### 🔄 Partially Implemented (Integration Points)

1. **Storefront API Integration**
   - Token storage: ✅
   - API calls: 📝 (placeholder JavaScript)
   - Product display: 📝 (requires frontend implementation)
   
2. **Product Management**
   - Product listing: 📝 (Storefront API integration needed)
   - Product detail: 📝 (Storefront API integration needed)
   - Variants: 📝 (Storefront API integration needed)

3. **Cart Integration**
   - Cart creation: ✅ (structure ready)
   - Cart API calls: 📝 (Storefront API integration needed)
   - Checkout redirect: ✅ (URL stored)

### 🎯 Extension Points

To complete the Storefront API integration:

1. **JavaScript Libraries**
   - Add Shopify Storefront API client
   - Implement GraphQL queries in frontend
   - Handle cart mutations

2. **Product Display**
   - Fetch products from Storefront API
   - Display product images
   - Show prices with currency
   - Render variant options

3. **Cart Operations**
   - Create cart via Storefront API
   - Add/update line items
   - Calculate totals
   - Generate checkout URLs

## Migration Path

### From Shopify Node.js Apps to CogMart

1. **Data Migration**
   - Export shops from Sequelize/PostgreSQL
   - Import into `oc_marketplace_shop` table
   - Map field names according to schema

2. **Frontend Migration**
   - Replace React components with Twig templates
   - Convert Apollo queries to AJAX calls
   - Adapt UI to Bootstrap 5

3. **API Adaptation**
   - Update client code to use REST-style endpoints
   - Convert GraphQL queries to parameter-based queries
   - Handle JSON responses instead of GraphQL results

## Performance Considerations

| Aspect | Shopify Node.js | CogMart PHP |
|--------|----------------|-------------|
| Async/Await | Native (Node.js) | Sequential PHP execution |
| Database Pooling | Sequelize connection pool | OpenCart DB connections |
| Caching | Redis/Memcached | OpenCart cache system |
| Sessions | Database-backed | OpenCart session management |
| API Calls | Non-blocking I/O | Sequential cURL/file_get_contents |

## Security Comparison

| Feature | Shopify Node.js | CogMart PHP |
|---------|----------------|-------------|
| SQL Injection | Sequelize ORM protection | OpenCart `$this->db->escape()` |
| XSS Prevention | React escaping | Twig auto-escaping |
| CSRF Protection | Token-based | OpenCart CSRF tokens |
| Authentication | Shopify OAuth | OpenCart admin/customer auth |
| API Security | Bearer tokens | Session-based + API keys |

## Conclusion

The CogMart marketplace extension successfully implements the core functionality of both Shopify Marketplace Kit applications (admin and buyer) in PHP using OpenCart 4.x conventions. The main architectural differences are:

1. **GraphQL → REST-like API**: Simplified query structure with similar capabilities
2. **React → Twig**: Server-side templates instead of client-side rendering
3. **Sequelize → OpenCart DB**: Direct SQL with escaping instead of ORM
4. **Apollo Client → AJAX**: Standard HTTP requests instead of GraphQL client

The extension provides all the necessary hooks and data structures to integrate with Shopify's Storefront API, with clear integration points marked in the code for completing the product display and cart functionality.
