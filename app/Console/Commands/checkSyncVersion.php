<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\SyncVersionRepository;
use DB;

class checkSyncVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkSyncVersion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check sync version menu';

    /**
     * Create a new command instance.
     *
     * @return void
     */

    const PROMOTION = 'promotion';
    const MENU = 'menu';

    private $msyncversion = null;

    public function __construct(SyncVersionRepository $msyncversion)
    {
        parent::__construct();
        $this->msyncversion = $msyncversion;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $last_version_menu = $this->msyncversion->getListSyncVersionByType(SYNC_VERSION_TYPE_MENU);
        if (!empty($last_version_menu)) {
            if (!$last_version_menu->is_sync) {
                $res = $this->menu($last_version_menu->file_name);
                if (!empty($res['result'])) {
                    $this->msyncversion->update(['is_sync' => 1], $last_version_menu->id);
                }
                echo json_encode($res).'<br>';
            }
        }

        $last_version_promotion = $this->msyncversion->getListSyncVersionByType(SYNC_VERSION_TYPE_PROMOTION);
        if (!empty($last_version_promotion)) {
            if (!$last_version_promotion->is_sync) {
                $res = $this->promotion($last_version_promotion->file_name);
                if (!empty($res['result'])) {
                    $this->msyncversion->update(['is_sync' => 1], $last_version_promotion->id);
                }
                die(json_encode($res));
            }
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function menu($file_name) {
        $res['result'] = false;

        $path = config("admin.path_sync") . $file_name;
        if (!file_exists($path)) {
            $res['msg'] = "Menu file does not found";
            $this->log_fail($res['msg'], self::MENU);
            die(json_encode($res));
        }

        $text = file_get_contents($path);
        $data = json_decode($text, true);
        $timestamp = $data['information']['timestamp'];

        DB::beginTransaction();
        try {
            if (!empty($data['menu_sku'])
                && $data['menu_category']
                && $data['menu']
                && $data['menu_variant']
                && $data['combo']
                && $data['combo_group']
                && $data['combo_menu']
                && $data['combo_menu_option']
                && $data['combo_variant']) {

                DB::table('menu_sku')->delete();
                DB::table('menu_category')->delete();
                DB::table('menu')->delete();
                DB::table('menu_variant')->delete();
                DB::table('combo')->delete();
                DB::table('combo_group')->delete();
                DB::table('combo_menu')->delete();
                DB::table('combo_menu_option')->delete();
                DB::table('combo_variant')->delete();
                DB::table('plucode')->delete();

                $this->import_menu_sku($data['menu_sku']);
                $this->import_menu_category($data['menu_category']);
                $this->import_menu($data['menu']);
                $this->import_menu_variant($data['menu_variant']);

                $this->import_combo($data['combo']);
                $this->import_combo_group($data['combo_group']);
                $this->import_combo_menu($data['combo_menu']);
                $this->import_combo_menu_option($data['combo_menu_option']);
                $this->import_combo_variant($data['combo_variant']);

                $this->import_plucode();

                DB::commit();
                $res['msg'] = "Sync Menu Successful";
                $res['result'] = true;
                $this->log_success($timestamp, $res['msg'], self::MENU);
                // clear cache menu after imported
                exec("rm -rf /var/www/posnew/pos1/application/cache/*");
            } else {
                DB::rollBack();
            }
        } catch (Exception $exc) {
            DB::rollBack();
            $res['msg'] = "ERROR : " . $exc->getMessage();
            $this->log_fail($res['msg'], self::MENU);
        }
        return $res;
    }

    private function import_menu_sku($data) {
        DB::table("menu_sku")->insert($data);
    }

    private function import_menu_category($data) {
        DB::table("menu_category")->insert($data);
    }

    private function import_menu($data) {
        foreach ($data as &$item) {
            $item['plucode'] = $this->clean($item['plucode']);
            DB::table("menu")->insert($item);
        }
    }

    private function import_menu_variant($data) {
        $variants = $this->getVariants();
        $addons = $this->getAddons();

        foreach ($data as &$item) {
            $variant_name = strtoupper($item['variant_name']);
            $addon_name = strtoupper($item['addon_name']);

            if (isset($variants[$variant_name])) {
                $variant_id = $variants[$variant_name];
            } else {
                $variant_id = DB::table('variant')->insertGetId(array('name' => $item['variant_name']));
                $variants[$variant_name] = $variant_id;
            }

            $addon_id = isset($addons[$addon_name]) ? $addons[$addon_name] : null;

            $item['variant_id'] = $variant_id;
            $item['addon_id'] = $addon_id;
        }

        $insert = array();

        foreach ($data as $row) {
            $insert[$row['menu_id'] . "-" . $row['variant_id']] = array(
                'menu_id' => $row['menu_id'],
                'variant_id' => $row['variant_id'],
                'addon_id' => null,
                'short_name' => $this->clean($row['variant_short_name']),
                'thumbnail' => $row['thumbnail'],
                'price' => $row['variant_price'],
                'gojek_price' => $row['gojek_variant_price'],
                'grab_price' => $row['grab_variant_price'],
                'others_price' => $row['others_variant_price'],
                'plucode' => $this->clean($row['variant_plucode'])
            );

            if ($row['addon_id'] != "") {
                $insert[$row['menu_id'] . "-" . $row['variant_id'] . "-" . $row['addon_id']] = array(
                    'menu_id' => $row['menu_id'],
                    'variant_id' => $row['variant_id'],
                    'addon_id' => $row['addon_id'],
                    'short_name' => $this->clean($row['addon_short_name']),
                    'thumbnail' => null,
                    'price' => $row['addon_price'],
                    'gojek_price' => $row['gojek_addon_price'],
                    'grab_price' => $row['grab_addon_price'],
                    'others_price' => $row['others_addon_price'],
                    'plucode' => $this->clean($row['addon_plucode'])
                );
            }
        }

        foreach ($insert as &$item_insert) {
            $key = $item_insert['menu_id'] . "-" . $item_insert['variant_id'];

            if ($item_insert['addon_id'] > 0 && isset($insert[$key])) {
                $item_insert['price'] += $insert[$key]['price'];
                $item_insert['gojek_price'] += $insert[$key]['gojek_price'];
                $item_insert['grab_price'] += $insert[$key]['grab_price'];
                $item_insert['others_price'] += $insert[$key]['others_price'];
            }
        }

        foreach ($insert as &$item) {
            if ($item['addon_id'] == null && $item['plucode'] == "") {
                $item['price'] = 0;
                $item['gojek_price'] = 0;
                $item['grab_price'] = 0;
                $item['others_price'] = 0;
            }
        }

        DB::table("menu_variant")->insert($insert);
    }

    private function import_combo($data) {
        DB::table("combo")->insert($data);
    }

    private function import_combo_group($data) {
        DB::table("combo_group")->insert($data);
    }

    private function import_combo_menu($data) {
        DB::table("combo_menu")->insert($data);
    }

    private function import_combo_menu_option($data) {
        DB::table("combo_menu_option")->insert($data);
    }

    private function import_combo_variant($data) {
        $variants = $this->getVariants();
        $addons = $this->getAddons();
        foreach ($data as &$item) {
            $variant_name = strtoupper($item['variant_name']);
            $addon_name = strtoupper($item['addon_name']);

            if (isset($variants[$variant_name])) {
                $variant_id = $variants[$variant_name];
            } else {
                $variant_id = DB::table('variant')->insertGetId(array('name' => $item['variant_name']));
                $variants[$variant_name] = $variant_id;
            }

            $addon_id = isset($addons[$addon_name]) ? $addons[$addon_name] : null;

            $item['variant_id'] = $variant_id;
            $item['addon_id'] = $addon_id;
        }
        $insert = array();
        foreach ($data as $row) {
            $insert[$row['combo_menu_id'] . "-" . $row['variant_id']] = array(
                'combo_menu_id' => $row['combo_menu_id'],
                'variant_id' => $row['variant_id'],
                'addon_id' => null,
                'short_name' => $row['short_name'],
                'price' => $row['variant_price'],
                'gojek_price' => $row['gojek_variant_price'],
                'grab_price' => $row['grab_variant_price'],
                'others_price' => $row['others_variant_price'],
                'plucode' => $row['addon_id'] != "" ? null : $this->clean($row['plucode'])
            );

            if ($row['addon_id'] != "") {
                $insert[$row['combo_menu_id'] . "-" . $row['variant_id'] . "-" . $row['addon_id']] = array(
                    'combo_menu_id' => $row['combo_menu_id'],
                    'variant_id' => $row['variant_id'],
                    'addon_id' => $row['addon_id'],
                    'short_name' => $row['short_name'],
                    'price' => $row['addon_price'],
                    'gojek_price' => $row['gojek_addon_price'],
                    'grab_price' => $row['grab_addon_price'],
                    'others_price' => $row['others_addon_price'],
                    'plucode' => $this->clean($row['plucode'])
                );
            }
        }

        DB::table("combo_variant")->insert($insert);
    }

    private function getVariants() {
        $variants = DB::table("variant")->get();
        $arr = array();
        foreach ($variants as $variant) {
            $arr[strtoupper($variant->name)] = $variant->id;
        }
        return $arr;
    }

    private function getAddons() {
        $addons = DB::table("addon")->get();
        $arr = array();
        foreach ($addons as $addon) {
            $arr[strtoupper($addon->name)] = $addon->id;
        }
        return $arr;
    }

    private function log_fail($message, $type = null) {
        DB::table("sync_log")->insert(array(
            'status' => 0,
            'type' => $type,
            'message' => $message,
            'timestamp' => 0,
            'created_date' => date("Y-m-d H:i:s")
        ));
    }

    private function log_success($timestamp = 0, $message = "SUCCESSFUL", $type = null) {
        DB::table("sync_log")->insert(array(
            'status' => 1,
            'type' => $type,
            'message' => $message,
            'timestamp' => $timestamp,
            'created_date' => date("Y-m-d H:i:s")
        ));
    }

    private function import_plucode() {
        $query_a = "INSERT INTO plucode (plucode, price, short_name, menu_id, menu_name, is_combo, category_id)
                    SELECT plucode, price, short_name, id AS menu_id, a.name AS menu_name, a.is_combo, a.category_id
                    FROM menu a
                    WHERE plucode <> ''";
        DB::statement($query_a);

        $query_b = "INSERT INTO plucode (plucode, price, short_name, menu_id, menu_name, is_combo, category_id, variant_id, addon_id)
                    SELECT a.plucode, a.price, a.short_name, b.id AS menu_id, b.name AS menu_name, b.is_combo, b.category_id, a.variant_id, a.addon_id
                    FROM menu_variant a, menu b
                    WHERE a.menu_id = b.id AND a.plucode <> ''";
        DB::statement($query_b);

        $query_c = "INSERT INTO plucode (plucode, price, short_name, menu_id, menu_name, is_combo, category_id, variant_id, addon_id)
                    SELECT c.plucode, c.price, c.short_name, a.id AS menu_id, a.name AS menu_name, a.is_combo, a.category_id, c.variant_id, c.addon_id
                    FROM menu a, combo_menu b, combo_variant c
                    WHERE a.id = b.menu_id AND b.id = c.combo_menu_id AND c.plucode <> ''
                    GROUP BY c.plucode";
        DB::statement($query_c);

        $query_d = "INSERT INTO plucode (plucode, price, short_name, menu_id, menu_name, is_combo, category_id)
                    SELECT a.plucode, a.price, a.short_text AS short_name, c.id AS menu_id, a.text AS menu_name, c.is_combo, c.category_id
                    FROM combo_menu_option a, combo_menu b, menu c
                    WHERE a.combo_menu_id = b.id AND b.menu_id = c.id";
        DB::statement($query_d);

        $query_e = "INSERT INTO plucode (plucode, price, short_name, menu_id, menu_name, is_combo, category_id)
                    SELECT a.plucode, a.price, a.short_name, a.menu_id, b.name as menu_name, b.is_combo, b.category_id
                    FROM combo_menu a, menu b
                    WHERE a.menu_id = b.id AND a.plucode <> ''";
        DB::statement($query_e);

        $query_f = "UPDATE plucode a, variant b SET a.`variant_name` = b.`name` WHERE a.`variant_id` = b.`id`";
        DB::statement($query_f);


        $query_g = "UPDATE plucode a, addon b SET a.`addon_name` = b.`name` WHERE a.`addon_id` = b.`id`";
        DB::statement($query_g);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function promotion($file_name) {
        $res['result'] = false;

        $path = config("admin.path_sync") . $file_name;
        if (!file_exists($path)) {
            $res['msg'] = "Promotion file not found";
            $this->log_fail($res['msg'], self::PROMOTION);
            die(json_encode($res));
        }

        $text = file_get_contents($path);
        $data = json_decode($text, true);
        $timestamp = $data['information']['timestamp'];

        DB::beginTransaction();
        try {
            if (!empty($data['promotion_master'])
                && $data['promotion_consumer']) {
                DB::table('promotion')->delete();
                DB::table('promotion_coupon')->delete();

                $this->import_promotion($data['promotion_master']);
                $this->import_promotion_coupon($data['promotion_consumer']);

                DB::commit();

                $res['msg'] = "Sync Promotion Successful";
                $res['result'] = true;
                $this->log_success($timestamp, $res['msg'], self::PROMOTION);
            } else {
                DB::rollBack();
            }
        } catch (Exception $exc) {
            DB::rollBack();
            $res['msg'] = "ERROR : " . $exc->getMessage();
            $this->log_fail($res['msg'], self::PROMOTION);
        }
        return $res;
    }

    private function import_promotion($data) {
        DB::table("promotion")->insert($data);
    }

    private function import_promotion_coupon($data) {
        DB::table("promotion_coupon")->insert($data);
    }

    function clean($string) {
        $string = str_replace("\n", "", $string);
        $string = str_replace("\N", "", $string);
        $string = str_replace("\r", "", $string);
        $string = str_replace("\R", "", $string);
        return $string;
    }
}
