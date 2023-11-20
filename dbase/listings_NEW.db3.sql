BEGIN TRANSACTION;
CREATE TABLE IF NOT EXISTS "changes" (
	"id"	INT,
	"changes"	TEXT,
	"user"	INT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "comps_ids" (
	"id"	INT,
	"comp1"	REAL,
	"comp2"	REAL,
	"comp3"	REAL,
	"id1"	TEXT,
	"id2"	TEXT,
	"id3"	TEXT,
	"type1"	INT,
	"type2"	INT,
	"type3"	INT,
	"source"	TEXT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "config_fees" (
	"type"	TEXT,
	"value"	TEXT,
	"id"	INT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "deletes" (
	"id"	INT,
	"deletes"	TEXT,
	"user"	INT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "header_colour_selection" (
	"platform"	TEXT,
	"id"	INT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "header_colours" (
	"id"	INT,
	"bg-color"	TEXT,
	"fg-color"	TEXT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "listings" (
	"id_lkup"	INT,
	"key"	TEXT,
	"cat_id"	TEXT,
	"group_"	TEXT,
	"ignore_zero_vat"	INT,
	"product_name"	TEXT,
	"packing"	TEXT,
	"packaging_band"	INT,
	"lowest_variation_weight"	INT,
	"variation"	INT,
	"pp2"	REAL,
	"remove"	INT,
	"timestamp"	INT,
	PRIMARY KEY("id_lkup")
);
CREATE TABLE IF NOT EXISTS "listings_amazon" (
	"id"	INT,
	"prev_price"	REAL,
	"new_price"	REAL,
	"perc_advertising"	INT,
	"notes"	TEXT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "listings_couriers" (
	"id"	INT,
	"courier"	INT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "listings_ebay" (
	"id"	INT,
	"prev_price"	REAL,
	"new_price"	REAL,
	"perc_advertising"	INT,
	"notes"	TEXT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "listings_prime" (
	"id"	INT,
	"prev_price"	REAL,
	"new_price"	REAL,
	"perc_advertising"	INT,
	"notes"	TEXT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "listings_web" (
	"id"	INT,
	"prev_price"	REAL,
	"new_price"	REAL,
	"perc_advertising"	INT,
	"notes"	TEXT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "lookup_couriers_plus_fuel" (
	"name"	TEXT,
	"courier"	TEXT,
	"cost"	TEXT,
	"fuel"	TEXT,
	"weight"	TEXT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "lookup_postage_bands" (
	"band"	TEXT,
	"cost"	TEXT,
	"max_weight"	TEXT,
	"example_packaging"	TEXT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "lookup_prod_cats" (
	"cat"	TEXT,
	"cat_id"	TEXT,
	"product_cat"	TEXT,
	"vat_rate"	INT,
	"timestamp"	INT,
	PRIMARY KEY("cat_id")
);
CREATE TABLE IF NOT EXISTS "multi_cpu" (
	"key"	INT,
	"keys"	TEXT,
	"percs"	TEXT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "platforms" (
	"id"	TEXT,
	"txt"	TEXT,
	"platform_fees"	int,
	"projection_20perc"	INT,
	"no_price_matrix"	INT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "prime_couriers" (
	"id"	INT,
	"courier"	INT,
	"timestamp"	INT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "rooms_lookup" (
	"id"	TEXT,
	"room"	TEXT,
	PRIMARY KEY("id")
);
CREATE TABLE IF NOT EXISTS "skus" (
	"id"	INT,
	"sku"	TEXT,
	"source"	TEXT,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "tub_costs" (
	"greater_than_15"	text,
	"greater_than_10"	text,
	"greater_than_5"	text,
	"greater_than_1"	text,
	"else"	text,
	"timestamp"	INT
);
CREATE TABLE IF NOT EXISTS "user" (
	"id"	INT,
	"name"	TEXT,
	PRIMARY KEY("id")
);
COMMIT;
