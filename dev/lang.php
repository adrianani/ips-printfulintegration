<?php

$lang = array(
	'__app_printfulintegration'	=> "Printful Commerce Integration",
	'printful_variants_no' => "{# [1:variant][?:variants]}",
	'printful_add_to_store' => "Mark for import",
	'menutab__printfulintegration' => 'Printful integration',
	'menu__printfulintegration_overview' => "Overview",
	'menu__printfulintegration_overview_settings' => "Settings",
	'printful_api_key' => "API Key",
	'menu__printfulintegration_overview_dashboard' => "Dashboard",
	'menu__printfulintegration_overview_products' => "Products",
	'menutab__printfulintegration_icon' => "cart-plus",
	'printful_search' => "Filter items by name",
	'printful_search_empty' => "The search into your printful store has returned no items :(",
	'printful_no_api_key' => "It looks like you haven't set your API key, please go to the <a href='%s' />Settings</a> page and complete the API Key field with your Printful Key.",
	'printful_category_title' => "Title",
	'menu__printfulintegration_overview_products_add_child' => "Create subcategory",
	'printful_category_parent' => "Parent",
	'printful_add_product' => "Import Printful Products",
	'printful_import_progress' => '%s out of %s',
	'printful_add_to_cart' => "Add to cart",
	'printful_item' => "Merch item",
	'printful_item_quantity' => "Quantity",
	'printful_item_color' => "Color",
	'printful_item_size' => "Size",
	'printful_productColors' => "Available in {!#[0:only one color][?:%s colors]}",
	'printful_cart' => "Your merch",
	'printful_added_to_cart' => "Item was added to your merch cart",
	'printful_item_removed' => "Item was removed from your cart",
	'printful_item_quantity_updated' => "Item quantity updated",
	'printful_cleared_cart' => "Merch cart is empty now",
	'printful_empty_cart' => "You have no merch in the cart right now",
	'printful_go_shopping' => "Go to the merch store",
	'shipping_method_option' => "%s - <span class='ipsType_small'>%s</span>",
	'choose_shipping_address' => "Shipping address",
	'choose_shipping_method' => "Shipping method",
	'shipping_method' => "Shipping: %s",
	'frontnavigation_printfulintegration' => "Merch",
	'frontnavigation_printfulintegration_store' => "Store",
	'printful_store_404' => "No items found matching your search criteria",
	'printful_dashboard_daily' => "{# [1:ORDER][?:ORDERS] today}",
	'printful_dashboard_weekly' => "{# [1:ORDER][?:ORDERS] in the past 7 days}",
	'printful_dashboard_monthly' => "{# [1:ORDER][?:ORDERS] in the past 30 days}",
	'printful_dashboard_profit' => "earnings this month",
	'printful_dashboard_quickstats' => "Sales overview",
	'printful_income_overview' => "Total Income",
	'printful_check_order' => "Open printful order",
	'printful_order_id' => "Printful Order",
	'printful_order_total' => "Printful Costs",
	'profit' => "Earnings",
	'printful_packing_slip_data' => "Packing slip data",
	'printful_ps_email' => "E-mail address",
	'printful_ps_email_desc' => "Customer service email",
	'printful_ps_phone' => "Phone number",
	'printful_ps_phone_desc' => "Customer service phone. Should be formatted like this: 111-222-3333",
	'printful_ps_message' => "Message",
	'printful_ps_message_desc' => "Custom packing slip message",
	'printful_process' => "Import selected products",
	'bad_request' => "Bad request",
	'printful_import_complete' => "Products imported",
	'printful_cancel' => "Cancel",
	'printful_product_pricing' => "Editing pricing: %s",
	'printful_variant_name' => "Variant",
	'printful_no_pricing_sync' => "Pricing changes here don't affect your Printful's store. (they don't sync)",
	'printful_not_available' => "Product not available",
	'printful_pricing' => "Edit prices",
	'printful_product_title' => "Title",
	'printful_product_images' => "Images",
	'printful_tab_api' => "API Settings",
	'printful_tab_store' => "Store",
	'printful_methods' => "Payment methods",
	'printful_methods_desc' => "Customers will only be able to pay for this product and renewals with the payment methods selected.",
	'printful_use_exchange_api' => "Use exchange API",
	'printful_use_exchange_api_desc' => "Whether to use or not the <a href='http://exchangeratesapi.io/' target='_blank' rel='nofollow'>exchangeratesapi.io</a>, API called every 12 hours, for price conversion. <div class='ipsType_negative'><i class='fa fa-exclamation-circle'></i> API values will still be used for values that are set to 0 even if this setting is disabled.</div>",
	'printful_your_val' => "Your value",
	'printful_api_val' => "API value",
	'printful_price_exchange' => "Price exchange",
	'printful_price_exchange_desc' => "If this is enabled products that don't have a price set for the user currency will be converted else they will be displayed as not available",
	'printful_orders' => "Orders",
	'printful_product_desc' => "Description",
	'printful_product_description' => "Product description",
	'printful_product_enabled' => "Show product in the store?",
	'printful_product_enabled_default' => "Show products in the store after import?",
	'menu__printfulintegration_overview_emails' => "Email campaigns",
	'printful_email_campaign' => "Email campaign",
	'pe_title' => "Subject",
	'pe_type' => "Type",
	'pe_order' => "Order complete",
	'pe_interval' => "Time interval",
	'pe_order_content' => "Content",
	'pe_interval_content' => "Content",
	'pe_groups' => "Groups to include",
	'pe_var_order_id' => "Order ID",
	'pe_var_order_url' => "Order URL",
	'pe_var_order_total' => "Order costs including printful costs and earnings",
	'pe_var_member_id' => "Member ID",
	'pe_var_member_name' => "Member name",
	'pe_var_member_joined' => "Member register date time",
	'pe_var_member_url' => "Member profile URL",
	'pe_var_member_last_visit' => "The last time the member visited this website",
	'pe_var_member_posts' => "Member's content count",
	'pe_var_member_total_expenses' => "Total value of merch orders of this customer",
	'pe_var_suite_name' => "Community name",
	'pe_var_suite_url' => "Community URL",
	'pe_var_suite_members' => "Total number of registered members",
	'pe_var_suite_posts' => "Total content count",
	'pe_var_suite_most_active' => "Most concurrent members online",
	'pe_var_suite_most_active_date' => "The date time at which the most concurrent members online took place.",
	'pe_var_merch_url' => "Merch store URL",
	'pe_var_merch_new_products' => "A list with the 5 newest products in the merch store",
	'pe_opted_out' => "Members which opted out of bulk emails (newsletter) will not receive time interval emails."
);
