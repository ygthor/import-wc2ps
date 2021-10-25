<?php

include 'db_config.php';

// $WORDPRESS_DB_HOST = 'localhost';
// $WORDPRESS_DATABASE = '';
// $WORDPRESS_USERNAME = '';
// $WORDPRESS_PASSWORD = '';
// $db_wp = new db([
//     'host' => $WORDPRESS_DB_HOST,
//     'database' => $WORDPRESS_DATABASE,
//     'username' => $WORDPRESS_USERNAME,
//     'password' => $WORDPRESS_PASSWORD,
// ]);


$PRESTASHOP_DB_HOST = 'localhost';
$PRESTASHOP_DATABASE = 'mybidday_onemart';
$PRESTASHOP_USERNAME = 'root';
$PRESTASHOP_PASSWORD = '';

global $db_ps;
$db_ps = new db([
    'host' => $PRESTASHOP_DB_HOST,
    'database' => $PRESTASHOP_DATABASE,
    'username' => $PRESTASHOP_USERNAME,
    'password' => $PRESTASHOP_PASSWORD,
]);

//Change the file name as you need
$wordpress_product_export_file_name = "wc-product-export.csv";
$temp_database_table_name = 'temp_wordpress_export';
$upload_directory = 'uploads';
$old_upload_path = '';


$file = file_get_contents($wordpress_product_export_file_name);

$row = 0;
/*
        [0] => ﻿ID
    [1] => Type
    [2] => SKU
    [3] => Name
    [4] => Published
    [5] => Is featured?
    [6] => Visibility in catalog
    [7] => Short description
    [8] => Description
    [9] => Date sale price starts
    [10] => Date sale price ends
    [11] => Tax status
    [12] => Tax class
    [13] => In stock?
    [14] => Stock
    [15] => Low stock amount
    [16] => Backorders allowed?
    [17] => Sold individually?
    [18] => Weight (kg)
    [19] => Length (cm)
    [20] => Width (cm)
    [21] => Height (cm)
    [22] => Allow customer reviews?
    [23] => Purchase note
    [24] => Sale price
    [25] => Regular price
    [26] => Categories
    [27] => Tags
    [28] => Shipping class
    [29] => Images
    [30] => Download limit
    [31] => Download expiry days
    [32] => Parent
    [33] => Grouped products
    [34] => Upsells
    [35] => Cross-sells
    [36] => External URL
    [37] => Button text
    [38] => Position
    [39] => Attribute 1 name
    [40] => Attribute 1 value(s)
    [41] => Attribute 1 visible
    [42] => Attribute 1 global
*/

if (($handle = fopen($wordpress_product_export_file_name, "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

        //VALIDATION on CATEGORY
        // if ($data['0'] == 24767) {
        //     dump($data);
        //     $category = $data['26'];
        //     createCategoty($category);
        // } else {
        //     continue;
        // }

        echo "<h4>#$row - {$data[0]}</h4>";

        if ($row == 0) {
            //IS COLUMN ROW CAN IGNORE
            $row++;
            continue;
        }


        //create category for COL 26
        $category = $data['26'];
        createCategoty($category);

        $product_type = $data[1];
        $product_sku = $data[2];
        $product_name = $data[3];
        $published = $data[4];
        $featured = $data[5];
        $visibility = $data[6];
        $short_description = $data[7];
        $description = $data[8];
        $date_of_sales_price_start = $data[9];
        $date_of_sales_price_end = $data[10];
        $taxable = $data[11];
        $tax_class = $data[12];
        $in_stock = $data[13];//1/0
        $stock = $data[14];
        $low_stock = $data[15];
        $backorder_allowed = $data[16];
        $sold_individually = $data[17];
        $weight = $data[18];
        $length = $data[19];
        $weidth = $data[20];
        $height = $data[21];
        $allow_customer_review = $data[22];
        $purchase_note = $data[23];
        $sales_price = $data[24];
        $regular_price = $data[25];
        //$backorder = $data[26];//category
        $tags = $data[27];
        $shipping_class = $data[28];
        $images = $data[29];
        $download_limit = $data[30];
        $download_expiry_day = $data[31];
        $parent = $data[32];
        $grouped_product = $data[33];
        $upsells = $data[34];
        $cross_sell = $data[35];
        $external_url = $data[36];
        $button_text = $data[37];
        $position = $data[38];
        $attr_1_name = $data[38];
        $attr_1_value = $data[39];
        $attr_1_visible = $data[40];
        $attr_1_global = $data[41];

        dd($data);


        //dump($data);


        $row++;
    }
    fclose($handle);
    dd('Success');
}


function meta_link($c)
{
    $c = htmlspecialchars_decode($c);
    $meta = preg_replace('/[^a-z_\-0-9]/i', ' ', $c);
    $meta = preg_replace('/\s+/', ' ', $meta);
    $meta = strtolower($meta);
    $meta = str_replace(' ', '-', $meta);
    return $meta;
}

/* Import FUNCTIOn */
function createCategoty($cat)
{
    if ($cat == '' || $cat == null) return;

    global $db_ps;
    $category_multi_arr = explode(',', $cat);

    foreach ($category_multi_arr as $cat) {
        $cat = str_replace('\\', '', $cat);
        $cat = trim($cat);
        $category_arr = explode(' > ', $cat);

        $id_parent = 2;
        $id_shop_default = 1;
        $depth = 1;

        foreach ($category_arr as $c) {
            $c = str_replace('\\', '', $c);
            $c = str_replace('&amp;', '&', $c);
            $c = $db_ps->real_escape_string($c);
            
            //check cat EXISTS
            $sql_chk = "SELECT * FROM ps_category_lang WHERE name='" . $c . "'";
            $data = $db_ps->first($sql_chk);

            if ($data == null) {

                //CREATE CAT
                $insert_ps_category = [
                    'id_parent' => $id_parent,
                    'id_shop_default' => $id_shop_default,
                    'level_depth' => $depth,
                    'nleft' => 0, //automate by prestashop
                    'nright' => 0, //automate by prestashop
                    'active' => 1,
                    'date_add' => date('Y-m-d'),
                    'date_upd' => date('Y-m-d'),
                    'position' => 0, // sort level in same category
                    'is_root_category' => 0,
                ];
                $category_id = $db_ps->insert_from_arr('ps_category', $insert_ps_category);
                $update_ps_category = [
                    'nleft' => $category_id
                ];
                $db_ps->update_from_arr_by_id('ps_category', $update_ps_category, $category_id, 'id_category');


                //Insert user group and category group
                foreach ([1, 2, 3] as $i) {
                    $insert_ps_category_group = [
                        'id_category' => $category_id,
                        'id_group' => $i,
                    ];
                    $group_id = $db_ps->insert_from_arr('ps_category_group', $insert_ps_category_group);
                }

                //Insert user group and category group
                foreach ([1, 2] as $i) {
                    $insert_ps_category_lang = [
                        'id_category' => $category_id,
                        'id_lang' => $i,
                        'id_shop' => $id_shop_default,
                        'name' => $c,
                        'description' => '',
                        'link_rewrite' => meta_link($c),
                    ];
                    $db_ps->insert_from_arr('ps_category_lang', $insert_ps_category_lang);
                }

                $insert_ps_category_shop = [
                    'id_category' => $category_id,
                    'id_shop' => $id_shop_default,
                ];
                $db_ps->insert_from_arr('ps_category_shop', $insert_ps_category_shop);

                //GET FOR
                $sql_chk = "SELECT * FROM ps_category_lang WHERE name='" . $c . "'";
                $data = $db_ps->first($sql_chk);
                echo('New Category Added: ' . $c.'<br>');
            }

            //go to next loop
            $id_parent = $data['id_category'];
            $depth++;
        }
    }
}


/*
    Helper Function
*/
function dump($v = 'RANDOM_STR')
{
    echo "<pre style='background:#263238;color:white;padding:10px;margin:20px 0px'>";
    if ($v === null) {
        echo 'null';
    } elseif ($v === 'RANDOM_STR') {
        echo randstr();
    } else {
        print_r($v);
    }
    echo "</pre>";
}

function dd($v = 'RANDOM_STR')
{
    echo "<pre style='background:#000000;color:white;padding:10px;margin:20px 0px'>";
    if ($v === null) {
        echo 'null';
    } elseif ($v == 'RANDOM_STR') {
        echo randstr();
    } else {
        print_r($v);
    }
    echo "</pre>";
    echo "<hr>";
    echo "EXIT";
    echo "<hr>";
    exit;
}

function randstr($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
