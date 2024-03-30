<?php

namespace App\Http\Controllers;

use App\Models\web_rcp_rooms;
use App\Http\Requests\Storeweb_rcp_roomsRequest;
use App\Http\Requests\Updateweb_rcp_roomsRequest;
use App\Models\web_rcp_rooms_pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class
WebRcpRoomsController extends Controller
{
    public function addRooms(Request $request)
    {
        $ins = DB::table("web_rcp_rooms")
            ->insert([
                "name" => $request->input('name_room'),
                "type" => $request->input('type_room'),
                "detail" => $request->input('detail_room'),
                "price1" => 0,
                "price2" => 0,
                "price3" => 0,
                "price4" => 0,
                "status" => "N",
            ]);

        $slid = DB::table("web_rcp_rooms")
            ->select("id")
            ->orderBy("id", "DESC")
            ->limit(1)
            ->get();
        foreach ($slid as $asslid) {
            $runn = 0;
            foreach ($request['pic'] as $i) {
                $path = $request['pic'][$runn]->store("roomPic", "public");
                $inspic = DB::table("web_rcp_rooms_pics")
                    ->insert([
                        "id_room" => $asslid->id,
                        "path" => $path,
                    ]);
                $runn += 1;
            }
        }
        return redirect(url("/admin/rcp/rooms/add"))->with(["inserted" => "ok"]);
    }
    public function sl4RoomsHome()
    {
        return $sql = DB::table("web_rcp_rooms")
            ->select("*")
            ->orderBy("id", "DESC")
            ->get();
    }
    public function slpicById($id)
    {
        return $sql = DB::table("web_rcp_rooms_pics")
            ->select("path")
            ->where("id_room", "=", "$id")
            ->limit(1)
            ->get();
    }
    public function addAmenities(Request $request)
    {

        $ins = DB::table("web_rcp_rooms_amenities")
            ->insert([
                "name" => $request->name_amenities,
                "status" => 'N'
            ]);


        $slid = DB::table("web_rcp_rooms_amenities")
            ->select("id")
            ->orderBy("id", "DESC")
            ->limit(1)
            ->get();

        foreach ($slid as $asslid) {
            $runn = 0;
            foreach ($request['pic'] as $i) {
                $path = $request['pic'][$runn]->store("amenitiesPic", "public");
                $inspic = DB::table("web_rcp_rooms_amenities_pics")
                    ->insert([
                        "id_amenities" => $asslid->id,
                        "path" => $path,
                    ]);
                $runn += 1;
            }
        }

        return redirect(url("/admin/rcp/rooms/amenities/add"))->with(["inserted" => "ok"]);

    }
    public function sl4AmenitiesHome()
    {
        return $sl = DB::table("web_rcp_rooms_amenities")
            ->select("*")
            ->orderBy("id", "DESC")
            ->get();
    }
    public function sl4AmenitiesHomeAdded($id)
    {
        return $sl = DB::table("web_rcp_rooms_sub_amenities")
            ->select("*")
            ->where("id_rooms", "=", $id)
            ->orderBy("id", "DESC")
            ->get();
    }
    public function slAmenitiesPicById($id)
    {
        return $sl = DB::table("web_rcp_rooms_amenities_pics")
            ->select("path")
            ->where("id_amenities", "=", "$id")
            ->get();
    }
    public function delAmenities()
    {
        $sl4delpic1 = DB::table("web_rcp_rooms_amenities_pics")
            ->select("path")
            ->where('id_amenities', '=', $_GET['id'])
            ->get();
        foreach ($sl4delpic1 as $asdelpic) {
            Storage::delete("public/$asdelpic->path");
        }

        $del = DB::table("web_rcp_rooms_amenities")
            ->delete("$_GET[id]");

        $delpic = DB::table("web_rcp_rooms_amenities_pics")
            ->where("id_amenities", "=", $_GET['id'])
            ->delete();
        return redirect(url("/admin/rcp/rooms"))->with(["del" => "ok"]);
    }
    function lockAmenities()
    {
        $sql = DB::table("web_rcp_rooms_amenities")
            ->where("id", "=", $_GET['id'])
            ->update([
                'status' => $_GET['status']
            ]);
        return redirect(url("/admin/rcp/rooms"));
    }
    function delRoom()
    {
        $sqlsl = DB::table("web_rcp_rooms_pics")
            ->select("path")
            ->where("id_room", "=", $_GET['id'])
            ->get();
        foreach ($sqlsl as $assqlsl) {
            Storage::delete("public/$assqlsl->path");
        }

        $delpic = DB::table("web_rcp_rooms_pics")
            ->where("id_room", "=", $_GET['id'])
            ->delete();


        $sqldel = DB::table("web_rcp_rooms")
            ->where("id", "=", $_GET['id'])
            ->delete();

        DB::table("web_rcp_rooms_sub_amenities")
            ->where("id_rooms", '=' ,  $_GET['id'])
            ->delete();

        return redirect(url("/admin/rcp/rooms"))->with(["del" => "ok"]);
    }

    function slRoomByid($id)
    {
        return DB::table("web_rcp_rooms")
            ->select("*")
            ->where("id", "=", $id)
            ->get();
    }

    public function editRoom(Request $request)
    {

        DB::table("web_rcp_rooms")
            ->where("id", "=", $_GET['id'])
            ->update([
                "status" => $request->statusroom,
                "price1" => $request->price1,
                "price2" => $request->price2,
                "price3" => $request->price3,
                "price4" => $request->price4,
            ]);

        $chk = DB::table("web_rcp_rooms_sub_amenities")
            ->select('*')
            ->where('id_rooms', "=", $_GET['id'])
            ->get();

        $i = 0;
        foreach ($chk as $asi) {
            $i += 1;
        }
        if ($i == 0) {
            if (isset($_POST['chk'])) {
                $coutchk = count($_POST['chk']);
                $i2 = 0;
                foreach (range(1,$coutchk) as $asCountchk) {
                    DB::table("web_rcp_rooms_sub_amenities")
                        ->insert([
                            "id_rooms" => $_GET['id'],
                            "id_amenities" => $_POST['chk'][$i2],
                        ]);
                    $i2+=1;
                }
            }else{
                DB::table("web_rcp_rooms_sub_amenities")
                    ->where("id_rooms", '=' ,  $_GET['id'])
                    ->delete();
            }

        }
        if ($i > 0 ){
            if (isset($_POST['chk'])) {

                DB::table("web_rcp_rooms_sub_amenities")
                    ->where("id_rooms", '=' ,  $_GET['id'])
                    ->delete();

                $coutchk = count($_POST['chk']);
                $i2 = 0;
                foreach (range(1,$coutchk) as $asCountchk) {
                    DB::table("web_rcp_rooms_sub_amenities")
                        ->insert([
                            "id_rooms" => $_GET['id'],
                            "id_amenities" => $_POST['chk'][$i2],
                        ]);
                    $i2+=1;
                }

            }else{
                DB::table("web_rcp_rooms_sub_amenities")
                    ->where("id_rooms", '=' ,  $_GET['id'])
                    ->delete();
            }
        }

        return redirect(url("/admin/rcp/rooms"))->with(["edited" => "ok"]);
    }
    function _sl_all(){
        return DB::table('web_rcp_rooms')
            ->where('status' ,"=", "Y")
            ->orderBy('id', "DESC")
            ->get();
    }

    function _sl_all_limit3(){
        return DB::table('web_rcp_rooms')
            ->where('status' ,"=", "Y")
            ->orderBy('id', "DESC")
            ->limit(3)
            ->get();
    }

    function _sl_all_pic($id){
        return DB::table('web_rcp_rooms_pics')
            ->where('id_room', "=" , $id)
            ->get();
    }
    function _sl_amenities($id){
        return DB::table('web_rcp_rooms_sub_amenities')->where('id_rooms' , "=",$id)
            ->get();
    }
    function _amenities($id){
        return DB::table('web_rcp_rooms_amenities')
            ->where('id', "=", $id)
            ->where('status', '=' , 'Y')
            ->get();
    }
    function _amenities_pic($id){
        return DB::table('web_rcp_rooms_amenities_pics')
            ->where('id_amenities', "=", $id)
            ->get();
    }
    function _sl_room_id($id){
        return DB::table('web_rcp_rooms')->where('id', '=', $id)->get();
    }

}
