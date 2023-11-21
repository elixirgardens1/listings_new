# Listings

#contents
* [Listings DB Tables](#listings-db-tables)
* [Categories Operation](#categories-operation)
* [Listings View](#listings-view)
* [Platform Specific Listings Tables](#platform-specific-listings-tables)
* [Courier Lookup](#courier-lookup)
* [Competition Columns](#competition-columns)
* [URLs Column](#urls-column)
* [VAT Rates](#vat-rates)
* [Platform Fees](#platform-fees)


## Categories Operation
---------------

### Main category drop-down

Data is stored in `cats@stock_control.db3`

![Image of cats table](docs/img/cats_-_stock_control.db3.png)
```
<select name="cat">
Aggregates|agg
Bamboo|bam
Birds / Wildlife| bir
Bubble Insulation|bub
Coir Blocks|coi
Electrical|ele
etc.
```

### Sub category drop-down (assuming `Electrical` selected above)

Data is stored in `lookup_prods_cats@listings.db3`

![Image of lookup_prods_cats table](docs/img/lookup_prods_cats_-_listings.db3.png)
```
<select name="cat_id">
Diesel Heater|a227
Fan Heaters|a17
Gas Heater|a226
Soil Warming|a18
etc.
```
Data from the above tables is sorted alphabetically before displaying in the listings view drop-downs:

![Image of listings view - electrical/gas heater](docs/img/listings_view__elec_gas_heater.png)

---------------

## Listings View

The listings view is created from several database tables.

![Image of listings view - aggregates/rock salt](docs/img/listings_view__aggr_rocksalt.png)

`listings@listings.db3` is common to all platforms (Ebay, Amazon etc):

![Image of listings table](docs/img/listings-group_-_listings.db3.png)

In the Aggregates/Rock Salt example, Rock Salt's drop-down value is "a244" `<option value="a244" selected="">Rock Salt</option>` so 'listings' table records whose 'cat_id' equals 'a244' are selected.

The listing view sorts the results into groups. The first group are those whose 'group_' value equals 'a' (highlighted above). They're sort alphabetically by 'product_name' before displaying.

4 columns from the listings table are displayed in the listings view:

* product_name
* packaging_band
* lowest_variation_weight
* variation

### Platform Specific Listings Tables

Specific platform data (prices, notes etc) is stored in individual platform tables. Selecting 'Ebay' in the drop-down, for example, retrieves the records from the 'listings_ebay' table. The 'id' values in 'listings_ebay' are matched to the 'id_lkup' values in 'listings'. The following are group 'a' records:

`listings_ebay@listings.db3`

![Image of listings_ebay table](docs/img/listings_ebay_-_listings.db3.png)

4 columns from the listings_ebay table are displayed in the listings view:

* prev_price
* new_price
* perc_advertising
* notes

*** There is no 'notes' column in the listings view. Records that have note values display a notes icon in the 'Product Name' column. An icon mouseover causes the note's message to pop up:

![Image of record with a note](docs/img/listings_ebay_note.png)
![Image of notes icon](docs/img/notes_icon.png)

## Courier Lookup

The 'id' values in the 'listings_couriers' table are matched to the 'id_lkup' values in 'listings':

`listings_couriers@listings.db3`

![Image of listings_couriers table](docs/img/listings_couriers_-_listings.db3.png)

The courier value in 'listings_couriers' can then be matched to the 'rowid' value in the 'lookup_couriers_plus_fuel' table. The corresponding 'name' values are then displayed in the listings view 'Courier' column.

`lookup_couriers_plus_fuel@listings.db3`

![Image of lookup_couriers_plus_fuel table](docs/img/lookup_couriers_plus_fuel_-_listings.db3.png)

Listings view Courier values

![Image of courier names](docs/img/courier_names.png)

**NOTE.** The `listings_couriers` table is not used when '**Prime**' is selected (listings view drop-down). It uses the `prime_couriers` table.


## Competition Columns

Displays the price of up to 3 competitors. The displayed prices are also links to the competitor's listing (eg. https://www.ebay.co.uk/itm/373442522706).

The lookup is done via the 'id_lkup/listings' > 'id/comps_ids' method used previously.

![Image of competition columns](docs/img/competition.png)

`comps_ids@listings.db3`

![Image of comps_ids table](docs/img/comps_ids_-_listings.db3.png)

The id1, id2 & id3 fields contain the link id values that get appended to the URLs. The main URLs can be found in the `$sort_by_profit_urls` array (`incs/lookups.php`). If the 'source' value is 'e', then 'https://www.ebay.co.uk/itm/' is used. If 'a' then 'https://www.amazon.co.uk/dp/' etc.

```
$sort_by_profit_urls = [
    'e' => 'https://www.ebay.co.uk/itm/',
    'a' => 'https://www.amazon.co.uk/dp/',
    'p' => 'https://www.amazon.co.uk/dp/',
    'w' => 'https://elixirgardensupplies.co.uk/product/',
];
```

The 'type' field values are used to display the correct link titles - appear as a pop up on mouseover. It uses the `$link_titles` array (`incs/lookups.php`). If 'type1' value equals 1, then a `Like 4 Like` pop up will appear on mouseover (see previous **competition.png**).

```
$link_titles = [
    '1' => 'Like 4 Like',
    '2' => 'Cheapest',
    '3' => 'Most Popular',
    '4' => 'Out of Stock',
];
```

## URLs Column

Hovering over a URLs label, ('OURS' column) displays 3 links. These link to the Elixir listings on the selected platform (Amazon, Ebay etc). It uses the `$sort_by_profit_urls` array mentioned previously.

![Image of URLs](docs/img/urls.png)

The link ids are stored in the `sku_am_ebb` table ('stock_control.db3').

`sku_am_ebb@stock_control.db3`

![Image of sku_ids3 table](docs/img/sku_ids3-sku_am_ebb_-_stock_control.db3.png)

![Image of sku_ids2 table](docs/img/sku_ids2-sku_am_ebb_-_stock_control.db3.png)

![Image of sku_ids1 table](docs/img/sku_ids1-sku_am_ebb_-_stock_control.db3.png)

The skus get added by clicking the listing's `add/edit` button ('Skus' column). The skus with checkboxes, that appear in the pop up, show the skus that have already been added. The 3 skus in this example tally with the 'ids' (ebay platform) on the previous screenshots.

![Image of Skus add/edit](docs/img/skus_add_edit.png)


## VAT Rates

Some categories can have zero vat - eg. Bird Seed

![Image of zero vat](docs/img/zero_vat.png)

The rates are set in lookup_prod_cats@listings.db3

![Image of lookup_prod_cats](docs/img/lookup_prod_cats.png)

The majority are set to 20 (20%), but any rate can be set.


The vat rate calculation is: new price - new price / (1 + vat rate / 100).

The code is located in `incs/php_functions.php`:

![Image of vat_calc_php_functions](docs/img/vat_calc_php_functions.png)

and `js/js_form_fld_calculations.php`:

![Image of vat_calc_js_form_fld_calculations](docs/img/vat_calc_js_form_fld_calculations.png)


## Platform Fees

Platform fees are stored in the `config_fees@listings.db3`.  

![Image of platform_fees table](docs/img/platform_fees_-_listings.db3.png)

The code that sets the fees_val is located in `sort_by_profit.php`:

![Image of sort_by_profit_fees_val](docs/img/sort_by_profit_fees_val.png)


## Listings DB Tables

![Image of db_tables](docs/img/db_tables.png)
