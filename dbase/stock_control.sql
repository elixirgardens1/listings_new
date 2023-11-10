BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "cats" (
	"cat"	TEXT type UNIQUE,
	"name"	TEXT,
	PRIMARY KEY("cat")
);
CREATE TABLE IF NOT EXISTS "missing_skus" (
	"sku"	TEXT type UNIQUE,
	"date"	TEXT
);
CREATE TABLE IF NOT EXISTS "stock" (
	"key"	TEXT type UNIQUE,
	"pkg_qty"	INTEGER,
	"pkg_multiples"	INTEGER,
	"min_qty"	INTEGER,
	"qty"	INTEGER,
	"days_amb"	INTEGER,
	"days_red"	INTEGER
);
CREATE TABLE IF NOT EXISTS "sku_atts_new" (
	"sku"	TEXT type UNIQUE,
	"atts"	TEXT,
	"sku_description"	TEXT,
	"date"	TEXT
);
CREATE TABLE IF NOT EXISTS "expanded_sku_atts" (
	"sku_key"	TEXT type UNIQUE,
	"sku"	TEXT,
	"key"	TEXT,
	"atts"	TEXT
);
CREATE TABLE IF NOT EXISTS "weekly_product_sales" (
	"key"	TEXT,
	"product"	TEXT,
	"weekNo"	INT,
	"date"	TEXT,
	"year"	INT,
	"qty"	INT
);
CREATE TABLE IF NOT EXISTS "sku_atts" (
	"sku"	TEXT,
	"atts"	TEXT,
	PRIMARY KEY("sku")
);
CREATE TABLE IF NOT EXISTS "sku_room_lookup" (
	"sku"	TEXT,
	"room"	TEXT,
	PRIMARY KEY("sku")
);
CREATE TABLE IF NOT EXISTS "stock_qty" (
	"key"	TEXT,
	"qty"	INT,
	"date"	TEXT,
	PRIMARY KEY("key")
);
CREATE TABLE IF NOT EXISTS "rooms_lookup" (
	"room"	TEXT,
	PRIMARY KEY("room")
);
CREATE TABLE IF NOT EXISTS "sku_am_eb_new" (
	"sku"	TEXT,
	"am_id"	TEXT,
	"eb_id"	TEXT,
	"we_id"	TEXT,
	"pr_id"	TEXT,
	"ps_id"	TEXT,
	PRIMARY KEY("sku")
);
CREATE TABLE IF NOT EXISTS "updated_stock" (
	"key"	TEXT,
	"qty"	INTEGER,
	"deliveryID"	TEXT,
	"datetime"	TEXT
);
CREATE TABLE IF NOT EXISTS "product_rooms" (
	"key"	TEXT,
	"room"	TEXT,
	"shelf_location"	INTEGER,
	PRIMARY KEY("key")
);
CREATE TABLE IF NOT EXISTS "zero_vat_skus" (
	"sku"	TEXT,
	PRIMARY KEY("sku")
);
CREATE TABLE IF NOT EXISTS "ordered_stock" (
	"key"	TEXT,
	"qty"	INTEGER,
	"ord_num"	INTEGER,
	"del_num"	TEXT,
	"supplier"	TEXT,
	"status"	TEXT,
	"signed"	TEXT,
	"exp_del_date"	TEXT,
	"datetime"	TEXT,
	"item_cost"	INTEGER,
	"newShelf"	TEXT
);
CREATE TABLE IF NOT EXISTS "product_orders_prices" (
	"ord_num"	INTEGER,
	"supplier"	TEXT,
	"date_placed"	TEXT,
	"date_delivered"	TEXT,
	"ord_value"	INTEGER,
	"delivery_number"	TEXT,
	"status"	TEXT,
	PRIMARY KEY("ord_num")
);
CREATE TABLE IF NOT EXISTS "stock_change" (
	"key"	TEXT,
	"qty"	INTEGER,
	"date"	TEXT,
	"outOfStock"	INTEGER
);
CREATE TABLE IF NOT EXISTS "sku_stock" (
	"orderID sku"	TEXT type UNIQUE,
	"date"	TEXT
);
CREATE TABLE IF NOT EXISTS "merged_asins" (
	"sku"	TEXT,
	"previousAsin"	TEXT,
	"newAsin"	TEXT,
	"date"	INTEGER,
	PRIMARY KEY("sku")
);
CREATE TABLE IF NOT EXISTS "stock_admin" (
	"errorType"	TEXT,
	"description"	TEXT,
	"alert"	TEXT,
	"date"	INTEGER
);
CREATE TABLE IF NOT EXISTS "product_cost_edits" (
	"key"	TEXT UNIQUE,
	"previousCost"	INTEGER,
	"changeDate"	INTEGER,
	PRIMARY KEY("key")
);
CREATE TABLE IF NOT EXISTS "sku_am_eb" (
	"sku"	TEXT,
	"id"	TEXT,
	"platform"	TEXT,
	PRIMARY KEY("sku","platform")
);
CREATE TABLE IF NOT EXISTS "sku_stock_deducted" (
	"orderID"	TEXT,
	"sku"	TEXT,
	"qty"	INTEGER,
	"originalKey"	TEXT,
	"deductedKey"	TEXT,
	"originalKeyStock"	INTEGER,
	"deductedKeyStock"	INTEGER,
	"postOriginalKeyStock"	INTEGER,
	"postDeductedKeyStock"	INTEGER,
	"date"	INTEGER,
	UNIQUE("orderID","sku","originalKey")
);
CREATE TABLE IF NOT EXISTS "products" (
	"cat"	TEXT,
	"sub_cat"	TEXT,
	"key"	TEXT,
	"unit"	TEXT,
	"product"	TEXT,
	"product_cost"	INTEGER,
	"primary_supplier"	TEXT,
	"secondary_supplier"	TEXT,
	"consumable"	TEXT,
	"to_be_hidden"	TEXT,
	"outOfStock"	INTEGER,
	"yellowThreshold"	INTEGER,
	"redThreshold"	INTEGER,
	"stockDeductible"	INTEGER,
	"width"	INTEGER,
	"length"	INTEGER,
	"notes"	INTEGER,
	PRIMARY KEY("key")
);
CREATE TABLE IF NOT EXISTS "ghost_sku" (
	"sku"	TEXT,
	"title"	TEXT,
	PRIMARY KEY("sku")
);
COMMIT;
